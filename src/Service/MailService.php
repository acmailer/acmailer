<?php
namespace AcMailer\Service;

use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerAwareInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Exception\MailException;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use AcMailer\View\DefaultLayout;
use AcMailer\View\DefaultLayoutInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mail\Exception\ExceptionInterface as ZendMailException;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

/**
 * Wraps Zend\Mail functionality
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailService implements MailServiceInterface, EventManagerAwareInterface, MailListenerAwareInterface
{
    /**
     * @var \Zend\Mail\Message
     */
    private $message;
    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    private $transport;
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var EventManagerInterface
     */
    private $events;
    /**
     * @var array
     */
    private $attachments = [];
    /**
     * @var DefaultLayoutInterface
     */
    private $defaultLayout;

    /**
     * Creates a new MailService
     * @param Message $message
     * @param TransportInterface $transport
     * @param RendererInterface $renderer Renderer used to render templates, typically a PhpRenderer
     */
    public function __construct(Message $message, TransportInterface $transport, RendererInterface $renderer)
    {
        $this->message      = $message;
        $this->transport    = $transport;
        $this->renderer     = $renderer;
        $this->setDefaultLayout();
    }

    /**
     * Returns this service's message
     * @return \Zend\Mail\Message
     * @see \AcMailer\Service\MailServiceInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sends the mail
     * @return ResultInterface
     * @throws MailException
     */
    public function send()
    {
        $result = new MailResult();
        try {
            // Trigger pre send event
            $this->getEventManager()->trigger($this->createMailEvent());

            // Attach files before sending the email
            $this->attachFiles();

            // Try to send the message
            $this->transport->send($this->message);

            // Trigger post send event
            $this->getEventManager()->trigger($this->createMailEvent(MailEvent::EVENT_MAIL_POST_SEND, $result));
        } catch (\Exception $e) {
            $result = $this->createMailResultFromException($e);
            // Trigger send error event
            $this->getEventManager()->trigger($this->createMailEvent(MailEvent::EVENT_MAIL_SEND_ERROR, $result));

            // If the exception produced is not a Zend\Mail exception, rethrow it as a MailException
            if (! $e instanceof ZendMailException) {
                throw new MailException('An non Zend\Mail exception occurred', $e->getCode(), $e);
            }
        }

        return $result;
    }

    /**
     * Creates a new MailEvent object
     * @param ResultInterface $result
     * @param string $name
     * @return MailEvent
     */
    protected function createMailEvent($name = MailEvent::EVENT_MAIL_PRE_SEND, ResultInterface $result = null)
    {
        $event = new MailEvent($this, $name);
        if (isset($result)) {
            $event->setResult($result);
        }
        return $event;
    }

    /**
     * Creates a error MailResult from an exception
     * @param \Exception $e
     * @return MailResult
     */
    protected function createMailResultFromException(\Exception $e)
    {
        return new MailResult(false, $e->getMessage(), $e);
    }

    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body Email body
     * @param string $charset
     * @return $this Returns this MailService for chaining purposes
     * @throws InvalidArgumentException
     * @see \AcMailer\Service\MailServiceInterface::setBody()
     */
    public function setBody($body, $charset = null)
    {
        if (is_string($body)) {
            // Create a Mime\Part and wrap it into a Mime\Message
            $mimePart = new Mime\Part($body);
            $mimePart->type     = $body != strip_tags($body) ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;
            $mimePart->charset  = $charset ?: self::DEFAULT_CHARSET;
            $body = new Mime\Message();
            $body->setParts([$mimePart]);
        } elseif ($body instanceof Mime\Part) {
            // Overwrite the charset if the Part object if provided
            if (isset($charset)) {
                $body->charset = $charset;
            }
            // The body is a Mime\Part. Wrap it into a Mime\Message
            $mimeMessage = new Mime\Message();
            $mimeMessage->setParts([$body]);
            $body = $mimeMessage;
        }

        // If the body is not a string or a Mime\Message at this point, it is not a valid argument
        if (! is_string($body) && ! $body instanceof Mime\Message) {
            throw new InvalidArgumentException(sprintf(
                'Provided body is not valid. It should be one of "%s". %s provided',
                implode('", "', ['string', 'Zend\Mime\Part', 'Zend\Mime\Message']),
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        // The headers Content-type and Content-transfer-encoding are duplicated every time the body is set.
        // Removing them before setting the body prevents this error
        $this->message->getHeaders()->removeHeader('contenttype');
        $this->message->getHeaders()->removeHeader('contenttransferencoding');
        $this->message->setBody($body);
        return $this;
    }

    /**
     * Sets the body of this message from a template
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     * @see \AcMailer\Service\MailServiceInterface::setTemplate()
     */
    public function setTemplate($template, array $params = [])
    {
        if ($template instanceof ViewModel) {
            $view = $template;
        } else {
            $view = new ViewModel();
            $view->setTemplate($template)
                 ->setVariables($params);
        }

        // Check if a common layout has to be used
        if ($this->defaultLayout->hasModel()) {
            $layoutModel = $this->defaultLayout->getModel();
            $layoutModel->addChild($view, $this->defaultLayout->getTemplateCaptureTo());
            $view = $layoutModel;
        }
        // Render the template and all of its children
        $this->renderChildren($view);

        $charset = isset($params['charset']) ? $params['charset'] : null;
        $this->setBody($this->renderer->render($view), $charset);
    }

    /**
     * Sets the default layout to be used with all the templates set when calling setTemplate.
     *
     * @param DefaultLayoutInterface $layout
     * @return mixed
     */
    public function setDefaultLayout(DefaultLayoutInterface $layout = null)
    {
        $this->defaultLayout = isset($layout) ? $layout : new DefaultLayout();
    }

    /**
     * Renders template children.
     * Inspired on Zend\View\View::renderChildren, which recursively renders children models
     * @param ViewModel $model
     */
    protected function renderChildren(ViewModel $model)
    {
        if (! $model->hasChildren()) {
            return;
        }

        /* @var ViewModel $child */
        foreach ($model as $child) {
            $capture = $child->captureTo();
            if (empty($capture)) {
                continue;
            }

            // Recursively render children
            $this->renderChildren($child);
            $result = $this->renderer->render($child);

            if ($child->isAppend()) {
                $oldResult = $model->{$capture};
                $model->setVariable($capture, $oldResult . $result);
            } else {
                $model->setVariable($capture, $result);
            }
        }
    }

    /**
     * Attaches files to the message if any
     * @throws \Zend\Mime\Exception\InvalidArgumentException
     * @throws \Zend\Mail\Exception\InvalidArgumentException
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    protected function attachFiles()
    {
        if (count($this->attachments) === 0) {
            return;
        }

        // Get old message parts
        $mimeMessage = $this->message->getBody();
        if (is_string($mimeMessage)) {
            $originalBodyPart = new Mime\Part($mimeMessage);
            $isHtml = $mimeMessage !== strip_tags($mimeMessage);
            $originalBodyPart->type = $isHtml ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;

            // A Mime\Part body will be wrapped into a Mime\Message, ensuring we handle a Mime\Message after this point
            $this->setBody($originalBodyPart);
            $mimeMessage = $this->message->getBody();
        }
        $oldParts = $mimeMessage->getParts();

        // Generate a new Mime\Part for each attachment
        $attachmentParts = [];
        $info = null;
        foreach ($this->attachments as $key => $attachment) {
            $encodingAndDispositionAreSet = false;

            if ($attachment instanceof Mime\Part) {
                // If the attachment is already a Mime\Part object, just add it
                $part = $attachment;
                $encodingAndDispositionAreSet = true;
            } elseif (is_string($attachment) && is_file($attachment)) {
                // If the attachment is a string that corresponds to a file, process it and create a Mime\Part
                $info = $info !== null ? $info : new \finfo(FILEINFO_MIME_TYPE);
                // If the key is not defined, use the attachment's basename
                $key = is_string($key) ? $key : basename($attachment);

                $part = new Mime\Part(fopen($attachment, 'r+b'));
                $part->type = $info->file($attachment);
            } elseif (is_resource($attachment)) {
                // If the attachment is a resource, use it as the content for a new Mime\Part
                $part = new Mime\Part($attachment);
            } elseif (is_array($attachment)) {
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
                    if (method_exists($part, $method)) {
                        $part->{$method}($value);
                    }
                }
            } else {
                // Ignore any other kind of attachment
                continue;
            }

            // Overwrite the id and filename of the Mime\Part with provided key if any
            if (is_string($key)) {
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
        $body->setParts(array_merge($oldParts, $attachmentParts));
        $this->message->setBody($body);
    }

    /**
     * Sets the message subject
     * @param string $subject The subject of the message
     * @return $this Returns this MailService for chaining purposes
     * @deprecated Use $mailService->getMessage()->setSubject() instead
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * @param string|resource|array|Mime\Part $file
     * @param string|null $filename
     * @return $this
     */
    public function addAttachment($file, $filename = null)
    {
        if ($filename !== null) {
            $this->attachments[$filename] = $file;
        } else {
            $this->attachments[] = $file;
        }
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function addAttachments(array $files)
    {
        return $this->setAttachments(array_merge($this->attachments, $files));
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setAttachments(array $files)
    {
        $this->attachments = $files;
        return $this;
    }

    /**
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Inject an EventManager instance
     * @param EventManagerInterface $events
     * @return $this|void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_called_class(),
        ]);
        $this->events = $events;
        return $this;
    }
    /**
     * Retrieve the event manager
     * Lazy-loads an EventManager instance if none registered.
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (! isset($this->events)) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Attaches a new MailListenerInterface
     * @param MailListenerInterface $mailListener
     * @param int $priority
     * @return mixed|void
     */
    public function attachMailListener(MailListenerInterface $mailListener, $priority = 1)
    {
        $this->getEventManager()->attach($mailListener, $priority);
        return $this;
    }

    /**
     * Detaches provided MailListener
     * @param MailListenerInterface $mailListener
     * @return $this
     */
    public function detachMailListener(MailListenerInterface $mailListener)
    {
        $mailListener->detach($this->getEventManager());
        return $this;
    }

    /**
     * @param TransportInterface $transport
     * @return $this
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Returns the transport object that will be used to send the wrapped message
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param RendererInterface $renderer
     *
     * @return $this
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Returns the renderer object that will be used to render templates
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
}
