<?php
namespace AcMailer\Service;

use AcMailer\Result\MailResult;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
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
     * Tries to send the message, returning a MailResult object
     * @return ResultInterface
     */
    public function send()
    {
        $this->sendMethodCalled = true;
        if ($this->forceError) {
            return new MailResult(false, "Error!!");
        } else {
            return new MailResult();
        }
    }

    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body
     * @throws InvalidArgumentException
     */
    public function setBody($body)
    {
        // Do nothing
    }
    /**
     * Sets the template to be used to create the body of the email
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     */
    public function setTemplate($template, array $params = array())
    {
        // Do nothing
    }
    /**
     * Sets the message subject
     * @param string $subject
     */
    public function setSubject($subject)
    {
        // Do nothing
    }
    /**
     * Returns the message that is going to be sent when method send is called
     * @see \AcMailer\Service\MailServiceInterface::send()
     * @return \Zend\Mail\Message
     */
    public function getMessage()
    {
        return new Message();
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
     * Provides the path of a file that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param string $path
     */
    public function addAttachment($path)
    {
        // Do nothing
    }
    /**
     * Provides an array of paths of files that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param array $paths
     */
    public function addAttachments(array $paths)
    {
        // Do nothing
    }
    /**
     * Sets the list of paths of files that will be attached to the message while sending it,
     * discarding any previously defined attachment
     * @param array $paths
     */
    public function setAttachments(array $paths)
    {
        // Do nothing
    }
    /**
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments()
    {
        // Do nothing
    }

    /**
     * Returns the transport object that will be used to send the wrapped message
     * @return TransportInterface
     */
    public function getTransport()
    {
        // Do nothing
    }

    /**
     * Returns the renderer object that will be used to render templates
     * @return RendererInterface
     */
    public function getRenderer()
    {
        // Do nothing
    }

    /**
     * @param RendererInterface $renderer
     * @return mixed
     */
    public function setRenderer(RendererInterface $renderer)
    {
        // Do nothing
    }

    /**
     * @param TransportInterface $transport
     * @return mixed
     */
    public function setTransport(TransportInterface $transport)
    {
        // Do nothing
    }
}
