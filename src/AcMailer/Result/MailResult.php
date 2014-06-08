<?php
namespace AcMailer\Result;

/**
 * Object returned by send method in MailService
 * @see \AcMailer\Service\MailServiceInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailResult implements ResultInterface
{
    
    const DEFAULT_MESSAGE = "Success!!";
    
    /**
     * @var boolean
     */
    private $result;
    /**
     * @var string
     */
    private $message;
    
    public function __construct($result = true, $message = self::DEFAULT_MESSAGE)
    {
        $this->result   = $result;
        $this->message  = $message;
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * @see \AcMailer\Result\ResultInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * @see \AcMailer\Result\ResultInterface::isValid()
     */
    public function isValid()
    {
        return $this->getResult();
    }
    
}