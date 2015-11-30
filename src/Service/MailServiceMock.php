<?php
namespace AcMailer\Service;

use AcMailer\Result\MailResult;
use AcMailer\View\DefaultLayoutInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use AcMailer\Result\ResultInterface;
use AcMailer\Exception\InvalidArgumentException;

/**
 * This class is meant to supplant MailService when unit testing elements that depend on a MailServiceInterface.
 * Remember to always program to abstractions, never concretions.
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceMock implements MailServiceInterface
{
    /**
     * @var bool
     */
    private $sendMethodCalled = false;
    /**
     * @var bool
     */
    private $forceError = false;

    /**
     * @var Message
     */
    private $message;
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var array
     */
    private $attachments = [];

    public function __construct()
    {
        $this->message = new Message();
    }

    /**
     * Tries to send the message, returning a MailResult object
     * @return ResultInterface
     */
    public function send()
    {
        $this->sendMethodCalled = true;
        if ($this->forceError) {
            return new MailResult(false, 'Error!!');
        } else {
            return new MailResult();
        }
    }

    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body
     * @param string $charset
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setBody($body, $charset = null)
    {
        $this->message->setBody($body);
        return $this;
    }
    /**
     * Sets the template to be used to create the body of the email
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     * @return $this
     */
    public function setTemplate($template, array $params = [])
    {
        $this->message->setBody($template instanceof ViewModel ? 'ViewModel body' : $template);
        return $this;
    }
    /**
     * Sets the message subject
     * @param string $subject
     * @deprecated Use $mailService->getMessage()->setSubject() instead
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
    }
    /**
     * Returns the message that is going to be sent when method send is called
     * @see \AcMailer\Service\MailServiceInterface::send()
     * @return \Zend\Mail\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Tells if send() method was previously called
     * @see \AcMailer\Service\MailServiceMock::send()
     * @return boolean True if send() was called, false otherwise
     */
    public function isSendMethodCalled()
    {
        return $this->sendMethodCalled;
    }

    /**
     * Sets the type of result produced when send method is called
     * @see \AcMailer\Service\MailServiceMock::send()
     * @param boolean $forceError True if an error should occur. False otherwise
     */
    public function setForceError($forceError)
    {
        $this->forceError = (bool) $forceError;
    }

    /**
     * @param string $path
     * @param null $filename
     * @return $this
     */
    public function addAttachment($path, $filename = null)
    {
        if (isset($filename)) {
            $this->attachments[$filename] = $path;
        } else {
            $this->attachments[] = $path;
        }
        return $this;
    }

    /**
     * @param array $paths
     * @return $this
     */
    public function addAttachments(array $paths)
    {
        return $this->setAttachments(array_merge($this->attachments, $paths));
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
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
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
     * Returns the renderer object that will be used to render templates
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param RendererInterface $renderer
     * @return mixed
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @param TransportInterface $transport
     * @return mixed
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Sets the default layout to be used with all the templates set when calling setTemplate.
     *
     * @param DefaultLayoutInterface $layout
     * @return mixed
     */
    public function setDefaultLayout(DefaultLayoutInterface $layout = null)
    {
        // Do nothing
    }
}
