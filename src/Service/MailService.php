<?php
declare(strict_types=1);

namespace AcMailer\Service;

use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerAwareInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Mail\MessageFactory;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\SharedEventManager;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;
use Zend\Stdlib\ArrayUtils;

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
     * Creates a new MailService
     * @param TransportInterface $transport
     * @param TemplateRendererInterface $renderer
     * @param EmailBuilderInterface $emailBuilder
     * @param EventManagerInterface|null $events
     */
    public function __construct(
        TransportInterface $transport,
        TemplateRendererInterface $renderer,
        EmailBuilderInterface $emailBuilder,
        EventManagerInterface $events = null
    ) {
        $this->transport = $transport;
        $this->renderer = $renderer;
        $this->emailBuilder = $emailBuilder;
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

        if (! $email->hasTemplate()) {
            $body = $this->buildBody($email);

            // The headers Content-type and Content-transfer-encoding are duplicated every time the body is set.
            // Removing them before setting the body prevents this error
            $message->getHeaders()->removeHeader('contenttype');
            $message->getHeaders()->removeHeader('contenttransferencoding');
            $message->setBody($body);
        } else {
            $message->setBody($this->renderer->render($email->getTemplate(), $email->getTemplateParams()));
        }

        return $message;
    }

    /**
     * Sets the message body
     * @param Email $email
     * @return Mime\Message
     * @throws Mime\Exception\InvalidArgumentException
     */
    private function buildBody(Email $email): Mime\Message
    {
        $body = $email->getBody();

        if (\is_string($body)) {
            // Create a Mime\Part and wrap it into a Mime\Message
            $mimePart = new Mime\Part($body);
            $mimePart->type = $body !== \strip_tags($body) ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;
            $mimePart->charset = $email->getCharset();
            $body = new Mime\Message();
            $body->setParts([$mimePart]);
        } elseif ($body instanceof Mime\Part) {
            $body->charset = $email->getCharset();

            // The body is a Mime\Part. Wrap it into a Mime\Message
            $mimeMessage = new Mime\Message();
            $mimeMessage->setParts([$body]);
            $body = $mimeMessage;
        }

        return $body;
    }

    /**
     * Attaches files to the message if any
     * @param Message $message
     * @param Email $email
     * @throws Exception\InvalidArgumentException
     * @throws \Zend\Mail\Exception\InvalidArgumentException
     * @throws Mime\Exception\InvalidArgumentException
     */
    private function attachFiles(Message $message, Email $email)
    {
        if (! $email->hasAttachments()) {
            return;
        }
        $attachments = $email->getAttachments();

        // Process the attachments dir if any, and include the files in that folder
        $dir = $email->getAttachmentsDir();
        $path = $dir['path'] ?? null;
        $recursive = (bool) ($dir['recursive'] ?? false);
        if ($path !== null && \is_string($path) && \is_dir($path)) {
            $files = $recursive ? new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) : new \DirectoryIterator($path);

            /* @var \SplFileInfo $fileInfo */
            foreach ($files as $fileInfo) {
                if ($fileInfo->isDir()) {
                    continue;
                }
                $attachments[] = $fileInfo->getPathname();
            }
        }

        // Get old message parts
        /** @var string|Mime\Message|Mime\Part $mimeMessage */
        $mimeMessage = $message->getBody();
        if (\is_string($mimeMessage)) {
            $isHtml = $mimeMessage !== \strip_tags($mimeMessage);
            $originalBodyPart = new Mime\Part($mimeMessage);
            $originalBodyPart->type = $isHtml ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;

            $email->setBody($originalBodyPart);
            $mimeMessage = $this->buildBody($email);
        }
        $oldParts = $mimeMessage->getParts();

        // Generate a new Mime\Part for each attachment
        $attachmentParts = [];
        $info = null;
        foreach ($attachments as $key => $attachment) {
            $encodingAndDispositionAreSet = false;

            if ($attachment instanceof Mime\Part) {
                // If the attachment is already a Mime\Part object, just add it
                $part = $attachment;
                $encodingAndDispositionAreSet = true;
            } elseif (\is_string($attachment) && \is_file($attachment)) {
                // If the attachment is a string that corresponds to a file, process it and create a Mime\Part
                $info = $info ?? new \finfo(FILEINFO_MIME_TYPE);
                // If the key is not defined, use the attachment's \basename
                $key = \is_string($key) ? $key : \basename($attachment);

                $part = new Mime\Part(\fopen($attachment, 'r+b'));
                $part->type = $info->file($attachment);
            } elseif (\is_resource($attachment)) {
                // If the attachment is a resource, use it as the content for a new Mime\Part
                $part = new Mime\Part($attachment);
            } elseif (\is_array($attachment)) {
                // If the attachment is an array, map a Mime\Part object with the array properties
                $part = new Mime\Part();
                $encodingAndDispositionAreSet = true;
                // Set default values for certain properties in the Mime\Part object
                $attachment = ArrayUtils::merge([
                    'encoding' => Mime\Mime::ENCODING_BASE64,
                    'disposition' => Mime\Mime::DISPOSITION_ATTACHMENT,
                ], $attachment);
                foreach ($attachment as $property => $value) {
                    $method = 'set' . $property;
                    if (\method_exists($part, $method)) {
                        $part->{$method}($value);
                    }
                }
            } else {
                // Ignore any other kind of attachment
                continue;
            }

            // Overwrite the id and filename of the Mime\Part with provided key if any
            if (\is_string($key)) {
                $part->id = $key;
                $part->filename = $key;
            }
            // Make sure encoding and disposition have a default value
            if (! $encodingAndDispositionAreSet) {
                $part->encoding = Mime\Mime::ENCODING_BASE64;
                $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            }
            $attachmentParts[] = $part;
        }

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
