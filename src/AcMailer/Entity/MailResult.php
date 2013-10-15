<?php
namespace AcMailer\Entity;

/**
 * Object returned by send method in MailService
 * @see \AcMailer\Service\MailServiceInterface
 * @author Alejandro Celaya Alastrué
 */
class MailResult
{
    
    private $result;
    private $message;
    
    public function __construct($result = true, $message = "Success!!") {
        $this->result   = $result;
        $this->message  = $message;
    }
    
    public function getResult() {
        return $this->result;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
}