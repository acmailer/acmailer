<?php
namespace AcMailer\Service;

/**
 * 
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceInterface
{
    
    /**
     * Tries to send the message, returning a MailResult object
     * @return \Application\Result\ResultInterface
     */
    public function send();
    
    /**
     * Returns the message that is going to be sent when method send is called
     * @see \AcMailer\Service\MailServiceInterface::send()
     * @return \Zend\Mail\Message
     */
    public function getMessage();
    
    public function setBody($body);
    public function setSubject($subject);
    
}