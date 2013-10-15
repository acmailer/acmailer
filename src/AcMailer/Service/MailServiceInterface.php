<?php
namespace AcMailer\Service;

/**
 * 
 * @author Alejandro Celaya Alastrué
 *
 */
interface MailServiceInterface
{
    
    /**
     * Tries to send the message, returning a MailResult object
     * @return \Application\Entity\MailResult
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