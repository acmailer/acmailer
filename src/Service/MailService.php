<?php

declare(strict_types=1);

namespace AcMailer\Service;

use AcMailer\Attachment\AttachmentParserManagerInterface;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use AcMailer\Event\EventDispatcherInterface;
use AcMailer\Event\MailListenerHandlerInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Event\PostSendEvent;
use AcMailer\Event\PreRenderEvent;
use AcMailer\Event\PreSendEvent;
use AcMailer\Event\SendErrorEvent;
use AcMailer\Exception;
use AcMailer\Mail\MessageFactory;
use AcMailer\Model\Attachment;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use AcMailer\View\MailViewRendererInterface;
use Laminas\Mail\Exception\InvalidArgumentException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

use function array_key_exists;
use function array_merge;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function strip_tags;

class MailService implements MailServiceInterface, MailListenerHandlerInterface
{
    private TransportInterface $transport;
    private MailViewRendererInterface $renderer;
    private EmailBuilderInterface $emailBuilder;
    private AttachmentParserManagerInterface $attachmentParserManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        TransportInterface $transport,
        MailViewRendererInterface $renderer,
        EmailBuilderInterface $emailBuilder,
        AttachmentParserManagerInterface $attachmentParserManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->transport = $transport;
        $this->renderer = $renderer;
        $this->emailBuilder = $emailBuilder;
        $this->attachmentParserManager = $attachmentParserManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string|array|Email $email
     * @param array $options
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\EmailNotFoundException
     * @throws Exception\MailException
     */
    public function send($email, array $options = []): ResultInterface
    {
        // Try to resolve the email to be sent
        if (is_string($email)) {
            $email = $this->emailBuilder->build($email, $options);
        } elseif (is_array($email)) {
            $email = $this->emailBuilder->build(Email::class, $email);
        } elseif (! $email instanceof Email) {
            throw Exception\InvalidArgumentException::fromValidTypes(
                ['string', 'array', Email::class],
                $email,
                'email',
            );
        }

        // Trigger the pre render event and then render the email's body in case it has to be composed from a template
        $this->dispatcher->dispatch(new PreRenderEvent($email));
        $this->renderEmailBody($email);

        // Trigger pre send event, and cancel email sending if any listener returned false
        $eventResp = $this->dispatcher->dispatch(new PreSendEvent($email));
        if ($eventResp->contains(false)) {
            return new MailResult($email, false);
        }

        try {
            // Build the message object to send
            $message = MessageFactory::createMessageFromEmail($email)->setBody(
                $this->buildBody($email->getBody(), $email->getCharset()),
            );
            $this->attachFiles($message, $email);
            $this->addCustomHeaders($message, $email);

            // Try to send the message
            $this->transport->send($message);

            // Trigger post send event
            $result = new MailResult($email);
            $this->dispatcher->dispatch(new PostSendEvent($email, $result));
            return $result;
        } catch (Throwable $e) {
            // Trigger error event, notifying listeners of the error
            $this->dispatcher->dispatch(new SendErrorEvent($email, new MailResult($email, false, $e)));

            throw Exception\MailException::fromThrowable($e);
        }
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    private function renderEmailBody(Email $email): void
    {
        if (! $email->hasTemplate()) {
            return;
        }

        $rawBody = $this->renderer->render(
            $email->getTemplate(),
            $this->injectLayoutParam($email->getTemplateParams()),
        );
        $email->setBody($rawBody);
    }

    private function injectLayoutParam(array $original): array
    {
        // When using Laminas/View in Mezzio, a layout could have been globally configured.
        // We have to override it unless explicitly provided. It won't affect other renderer implementations.
        if (! array_key_exists('layout', $original)) {
            $original['layout'] = false;
        }

        return $original;
    }

    /**
     * Sets the message body
     * @param string|Mime\Part|Mime\Message $body
     * @throws Mime\Exception\InvalidArgumentException
     */
    private function buildBody($body, string $charset): Mime\Message
    {
        if ($body instanceof Mime\Message) {
            return $body;
        }

        // If the body is a string, wrap it into a Mime\Part
        if (is_string($body)) {
            $mimePart = new Mime\Part($body);
            $mimePart->type = $body !== strip_tags($body) ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;
            $body = $mimePart;
        }

        $body->charset = $charset;
        $message = new Mime\Message();
        $message->setParts([$body]);
        return $message;
    }

    /**
     * @throws Exception\InvalidAttachmentException
     * @throws Exception\ServiceNotCreatedException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    private function attachFiles(Message $message, Email $email): void
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
            // If the attachment is an array with "parser_name" and "value" keys, cast it into an Attachment object
            if (is_array($attachment) && isset($attachment['parser_name'], $attachment['value'])) {
                $attachment = Attachment::fromArray($attachment);
            }

            $parserName = $this->resolveParserNameFromAttachment($attachment);
            if (! $this->attachmentParserManager->has($parserName)) {
                throw new Exception\ServiceNotCreatedException(
                    sprintf('The attachment parser "%s" could not be found', $parserName),
                );
            }

            /** @var AttachmentParserInterface $parser */
            $parser = $this->attachmentParserManager->get($parserName);
            $attachmentValue = $attachment instanceof Attachment ? $attachment->getValue() : $attachment;
            $part = $parser->parse($attachmentValue, is_string($key) ? $key : null);

            $part->charset = $email->getCharset();
            $attachmentParts[] = $part;
        }

        // Create a new body for the message, merging the attachment parts and all the old parts
        $body = new Mime\Message();
        $body->setParts(array_merge($oldParts, $attachmentParts));
        $message->setBody($body);
    }

    /**
     * @param string|resource|array|Mime\Part|Attachment $attachment
     */
    private function resolveParserNameFromAttachment($attachment): string
    {
        if ($attachment instanceof Attachment) {
            return $attachment->getParserName();
        }

        return is_object($attachment) ? get_class($attachment) : gettype($attachment);
    }

    private function addCustomHeaders(Message $message, Email $email): void
    {
        $headers = $message->getHeaders();
        foreach ($email->getCustomHeaders() as $headerName => $value) {
            $headers->addHeaderLine($headerName, $value);
        }
        $message->setHeaders($headers);
    }

    public function attachMailListener(MailListenerInterface $mailListener, int $priority = 1): void
    {
        $this->dispatcher->attachMailListener($mailListener, $priority);
    }

    public function detachMailListener(MailListenerInterface $mailListener): void
    {
        $this->dispatcher->detachMailListener($mailListener);
    }
}
