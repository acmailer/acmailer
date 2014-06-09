<?php
namespace AcMailer\Service;

use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListener;
use AcMailer\Event\MailListenerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Exception\RuntimeException;
use AcMailer\Result\ResultInterface;
use AcMailer\Result\MailResult;
use Zend\Mime\Mime;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use AcMailer\Exception\InvalidArgumentException;

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
     * @var \Zend\View\Renderer\PhpRenderer
     */
    private $renderer;
	/**
	 * @var EventManagerInterface
	 */
	private $events;
    /**
     * @var array
     */
    private $attachments = array();

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
     * @throws \Exception
     */
    public function send()
    {
        // Attach files before sending the email
        if (count($this->attachments) > 0) {
            $mimeMessage = $this->message->getBody();
            if (!$mimeMessage instanceof MimeMessage) {
                $this->setBody(new MimePart($mimeMessage));
                $mimeMessage = $this->message->getBody();
            }
            $bodyContent        = $mimeMessage->generateMessage();
            $bodyPart           = new MimePart($bodyContent);
            $bodyPart->type     = Mime::TYPE_HTML; // TODO
            $attachmentParts    = array();
            $info               = new \finfo(FILEINFO_MIME_TYPE);
            foreach ($this->attachments as $attachment) {
                if (!is_file($attachment)) {
                    continue; // If checked file is not valid, continue to the next
                }
                
                $part               = new MimePart(fopen($attachment, 'r'));
                $part->filename     = basename($attachment);
                $part->type         = $info->file($attachment);
                $part->encoding     = Mime::ENCODING_BASE64;
                $part->disposition  = Mime::DISPOSITION_ATTACHMENT;
                $attachmentParts[]  = $part;
            }
            array_unshift($attachmentParts, $bodyPart);
            $body = new MimeMessage();
            $body->setParts($attachmentParts);
            $this->message->setBody($body);
        }
        
        // Send the email
        try {
			// Trigger pre send event
			$event = new MailEvent($this);
			$this->getEventManager()->trigger($event);

            $this->transport->send($this->message);

			// Trigger post send event
			$event = new MailEvent($this, MailEvent::EVENT_MAIL_POST_SEND);
			$this->getEventManager()->trigger($event);

            return new MailResult();
        } catch (RuntimeException $e) {
			// Trigger send error event
			$event = new MailEvent($this, MailEvent::EVENT_MAIL_SEND_ERROR);
			$this->getEventManager()->trigger($event);

            return new MailResult(false, $e->getMessage());
        } catch (\Exception $e) {
			// Trigger send error event
			$event = new MailEvent($this, MailEvent::EVENT_MAIL_SEND_ERROR);
			$this->getEventManager()->trigger($event);

            throw $e;
		}
    }
    
    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body Email body
     * @return $this Returns this MailService for chaining purposes
     * @throws InvalidArgumentException
     * @see \AcMailer\Service\MailServiceInterface::setBody()
     */
    public function setBody($body)
    {
        // The body is HTML. Create a Mime\Part and wrap it into a Mime\Message
        if (is_string($body) && $body != strip_tags($body)) {
            $mimePart = new MimePart($body);
            $mimePart->charset  = "utf-8"; // TODO Allow this to be configured by options
            $mimePart->type     = Mime::TYPE_HTML;
            $body = new MimeMessage();
            $body->setParts(array($mimePart));
        // The body is a Mime\Part. Wrap it into a Mime\Message
        } elseif ($body instanceof MimePart) {
            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts(array($body));
            $body = $mimeMessage;
        }

        // If the body is not a string or a MimeMessage at this point, it is not a valid argument
        if (!is_string($body) && !($body instanceof MimeMessage)) {
            throw new InvalidArgumentException(sprintf(
                "Provided body is not valid. It should be a string, a Zend\\Mime\\Part or a Zend\\Mime\\Message. %s provided",
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        $this->message->setBody($body);
        return $this;
    }
    
    /**
     * Sets the body of this message from a template
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     * @see \AcMailer\Service\MailServiceInterface::setTemplate()
     */
    public function setTemplate($template, array $params = array())
    {
        if ($template instanceof ViewModel) {
            if ($template->hasChildren()) {
                $this->renderChildren($template);
            }
            $this->setBody($this->renderer->render($template));
            return;
        }

        $view = new ViewModel();
        $view->setTemplate($template)
             ->setVariables($params);
        $this->setBody($this->renderer->render($view));
    }

    /**
     * Renders template childrens.
     * Inspired on Zend\View\View implementation to recursively render child models
     * @param ViewModel $model
     * @see Zend\View\View::renderChildren
     */
    protected function renderChildren(ViewModel $model)
    {
        /* @var ViewModel $child */
        foreach ($model as $child) {
            $capture = $child->captureTo();
            if (!empty($capture)) {
                // Recursively render children
                if ($child->hasChildren()) {
                    $this->renderChildren($child);
                }
                $result = $this->renderer->render($child);

                if ($child->isAppend()) {
                    $oldResult=$model->{$capture};
                    $model->setVariable($capture, $oldResult . $result);
                } else {
                    $model->setVariable($capture, $result);
                }
            }
        }
    }
    
    /**
     * Sets the message subject
     * @param string $subject The subject of the message
     * @return $this Returns this MailService for chaining purposes
     * @see \AcMailer\Service\MailServiceInterface::setSubject()
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
        return $this;
    }
    
	/**
	 * @param string $path
	 * @return $this
	 */
	public function addAttachment($path)
    {
		$this->attachments[] = $path;
		return $this;
	}

	/**
	 * @param array $paths
	 * @return $this
	 */
	public function addAttachments(array $paths)
    {
		$this->attachments = array_merge($this->attachments, $paths);
		return $this;
	}

	/**
	 * @param array $paths
	 * @return $this
	 */
	public function setAttachments(array $paths)
    {
		$this->attachments = $paths;
		return $this;
	}

	/**
	 * Inject an EventManager instance
	 * @param EventManagerInterface $events
	 * @return $this|void
	 */
	public function setEventManager(EventManagerInterface $events)
    {
		$events->setIdentifiers(array(
			__CLASS__,
			get_called_class(),
		));
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
		if (!isset($this->events)) {
			$this->setEventManager(new EventManager());
        }

		return $this->events;
	}

	/**
	 * Attaches a new MailListener
	 * @param MailListener $mailListener
	 * @param int $priority
	 * @return mixed|void
	 */
	public function attachMailListener(MailListener $mailListener, $priority = 1)
    {
		$this->getEventManager()->attach(MailEvent::EVENT_MAIL_PRE_SEND, function (MailEvent $e) use ($mailListener) {
			$mailListener->onPreSend($e);
		}, $priority);
		$this->getEventManager()->attach(MailEvent::EVENT_MAIL_POST_SEND, function (MailEvent $e) use ($mailListener) {
			$mailListener->onPostSend($e);
		}, $priority);
		$this->getEventManager()->attach(MailEvent::EVENT_MAIL_SEND_ERROR, function (MailEvent $e) use ($mailListener) {
			$mailListener->onSendError($e);
		}, $priority);
	}

}