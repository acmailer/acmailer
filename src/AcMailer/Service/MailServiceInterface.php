<?php
namespace AcMailer\Service;

use AcMailer\Result\ResultInterface;

/**
 * Provides methods to be implemented by a valid MailService
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceInterface
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
     */
    public function setBody($body);
    
    /**
     * Sets the message subject
     * @param string $subject
     */
    public function setSubject($subject);
    
}