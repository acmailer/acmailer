<?php
namespace AcMailer\Service;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Mail\Transport\TransportAwareInterface;
use AcMailer\Result\ResultInterface;
use AcMailer\View\RendererAwareInterface;
use Zend\Mail\Transport\TransportInterface;
use Zend\View\Renderer\RendererInterface;

/**
 * Provides methods to be implemented by a valid MailService
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceInterface extends TransportAwareInterface, RendererAwareInterface
{
    /**
     * Tries to send the message, returning a MailResult object
     * @return ResultInterface
     */
    public function send();
    
    /**
     * Returns the message that is going to be sent when method send is called
     * @see \AcMailer\Service\MailServiceInterface::send()
     * @return \Zend\Mail\Message
     */
    public function getMessage();
    
    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body
     * @throws InvalidArgumentException
     */
    public function setBody($body);
    
    /**
     * Sets the template to be used to create the body of the email
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     */
    public function setTemplate($template, array $params = array());
    
    /**
     * Sets the message subject
     * @param string $subject
     */
    public function setSubject($subject);
    
    /**
     * Provides the path of a file that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param string $path
     */
    public function addAttachment($path);

    /**
     * Provides an array of paths of files that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param array $paths
     */
    public function addAttachments(array $paths);

    /**
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments();
    
    /**
     * Sets the list of paths of files that will be attached to the message while sending it,
     * discarding any previously defined attachment
     * @param array $paths
     */
    public function setAttachments(array $paths);
}
