<?php
namespace AcMailer\Entity;

/**
 * Object returned by send method in MailService
 * @see \AcMailer\Service\MailServiceInterface
 * @author Alejandro Celaya Alastrué
 * @see http://www.alejandrocelaya.com
 */
class MailResult
{
    
    /**
     * @var boolean
     */
    private $result;
    /**
     * @var string
     */
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
    
    /**
     * Tells if the MailService that produced this result was properly sent
     * @return boolean
     */
    public function isValid() {
        return $this->getResult();
    }
    
}