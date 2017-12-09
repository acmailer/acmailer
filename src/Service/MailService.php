<?php
declare(strict_types=1);

namespace AcMailer\Service;

use AcMailer\Attachment\AttachmentParserManagerInterface;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerAwareInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Mail\MessageFactory;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\SharedEventManager;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;

/**
 * Wraps Zend\Mail functionality
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailService implements MailServiceInterface, EventsCapableInterface, MailListenerAwareInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;
    /**
     * @var EventManagerInterface
     */
    private $events;
    /**
     * @var EmailBuilderInterface
     */
    private $emailBuilder;
    /**
     * @var AttachmentParserManagerInterface
     */
    private $attachmentParserManager;

    /**
     * Creates a new MailService
     * @param TransportInterface $transport
     * @param TemplateRendererInterface $renderer
     * @param EmailBuilderInterface $emailBuilder
     * @param AttachmentParserManagerInterface $attachmentParserManager
     * @param EventManagerInterface|null $events
     */
    public function __construct(
        TransportInterface $transport,
        TemplateRendererInterface $renderer,
        EmailBuilderInterface $emailBuilder,
        AttachmentParserManagerInterface $attachmentParserManager,
        EventManagerInterface $events = null
    ) {
        $this->transport = $transport;
        $this->renderer = $renderer;
        $this->emailBuilder = $emailBuilder;
        $this->attachmentParserManager = $attachmentParserManager;
        $this->events = $this->initEventManager($events);
    }

    private function initEventManager(EventManagerInterface $events = null): EventManagerInterface
    {
        $events = $events ?: new EventManager(new SharedEventManager());
        $events->setIdentifiers([
            __CLASS__,
            static::class,
        ]);
        return $events;
    }

    /**
     * Tries to send the message, returning a MailResult object
     * @param string|array|Email $email
     * @param array $options
     * @return ResultInterface
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\EmailNotFoundException
     * @throws Exception\MailException
     */
    public function send($email, array $options = []): ResultInterface
    {
        // Try to resolve the email to be sent
        if (\is_string($email)) {
            $email = $this->emailBuilder->build($email, $options);
        } elseif (\is_array($email)) {
            $email = $this->emailBuilder->build(Email::class, $email);
        } elseif (! $email instanceof Email) {
            throw Exception\InvalidArgumentException::fromValidTypes(['string', 'array', Email::class], $email);
        }

        // Trigger pre send event, an cancel email sending if any listener returned false
        $eventResp = $this->events->triggerEvent($this->createMailEvent($email));
        if ($eventResp->contains(false)) {
            return new MailResult($email, false);
        }

        try {
            // Build the message object to send
            $message = $this->createMessageFromEmail($email);
            $this->attachFiles($message, $email);

            // Try to send the message
            $this->transport->send($message);

            // Trigger post send event
            $result = new MailResult($email);
            $this->events->triggerEvent($this->createMailEvent($email, MailEvent::EVENT_MAIL_POST_SEND, $result));
            return $result;
        } catch (\Throwable $e) {
            // Trigger error event, notifying listeners of the error
            $this->events->triggerEvent($this->createMailEvent($email, MailEvent::EVENT_MAIL_SEND_ERROR, new MailResult(
                $email,
                false,
                $e
            )));

            throw new Exception\MailException('An error occurred while trying to send the email', $e->getCode(), $e);
        }
    }

    /**
     * Creates a new MailEvent object
     * @param Email $email
     * @param string $name
     * @param ResultInterface $result
     * @return MailEvent
     */
    private function createMailEvent(
        Email $email,
        $name = MailEvent::EVENT_MAIL_PRE_SEND,
        ResultInterface $result = null
    ): MailEvent {
        $event = new MailEvent($email, $name);
        if ($result !== null) {
            $event->setResult($result);
        }

        return $event;
    }

    private function createMessageFromEmail(Email $email): Message
    {
        $message = MessageFactory::createMessageFromEmail($email);
        $rawBody = $email->hasTemplate()
            ? $this->renderer->render($email->getTemplate(), $email->getTemplateParams())
            : $email->getBody();

        return $message->setBody($this->buildBody($rawBody, $email->getCharset()));
    }

    /**
     * Sets the message body
     * @param string|Mime\Part|Mime\Message $body
     * @param string $charset
     * @return Mime\Message
     * @throws Mime\Exception\InvalidArgumentException
     */
    private function buildBody($body, string $charset): Mime\Message
    {
        if ($body instanceof Mime\Message) {
            return $body;
        }

        // If the body is a string, wrap it into a Mime\Part
        if (\is_string($body)) {
            $mimePart = new Mime\Part($body);
            $mimePart->type = $body !== \strip_tags($body) ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;
            $body = $mimePart;
        }

        $body->charset = $charset;
        return (new Mime\Message())->setParts([$body]);
    }

    /**
     * Attaches files to the message if any
     * @param Message $message
     * @param Email $email
     * @throws Exception\InvalidAttachmentException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    private function attachFiles(Message $message, Email $email)
    {
        if (! $email->hasAttachments()) {
            return;
        }
        $attachments = $email->getComputedAttachments();

        // Get old message parts
        /** @var Mime\Message $mimeMessage */
        $mimeMessage = $message->getBody();
        $oldParts = $mimeMessage->getParts();

        // Generate a new Mime\Part for each attachment
        $attachmentParts = [];
        $info = null;
        foreach ($attachments as $key => $attachment) {
            $parserName = \is_object($attachment) ? \get_class($attachment) : \gettype($attachment);
            if (! $this->attachmentParserManager->has($parserName)) {
                continue;
            }

            /** @var AttachmentParserInterface $parser */
            $parser = $this->attachmentParserManager->get($parserName);
            $part = $parser->parse($attachment, \is_string($key) ? $key : null);

            $part->charset = $email->getCharset();
            $attachmentParts[] = $part;
        }

        // Create a new body for the message, merging the attachment parts and all the old parts
        $body = new Mime\Message();
        $body->setParts(\array_merge($oldParts, $attachmentParts));
        $message->setBody($body);
    }

    /**
     * Retrieve the event manager
     * Lazy-loads an EventManager instance if none registered.
     * @return EventManagerInterface
     */
    public function getEventManager(): EventManagerInterface
    {
        return $this->events;
    }

    /**
     * Attaches a new MailListenerInterface
     * @param MailListenerInterface $mailListener
     * @param int $priority
     * @return void
     */
    public function attachMailListener(MailListenerInterface $mailListener, $priority = 1)
    {
        $mailListener->attach($this->events, $priority);
    }

    /**
     * Detaches provided MailListener
     * @param MailListenerInterface $mailListener
     * @return void
     */
    public function detachMailListener(MailListenerInterface $mailListener)
    {
        $mailListener->detach($this->events);
    }
}
