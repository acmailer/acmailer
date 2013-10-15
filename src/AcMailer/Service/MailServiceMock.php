<?php
namespace AcMailer\Service;

use AcMailer\Service\MailServiceInterface;
use AcMailer\Entity\MailResult;
use Zend\Mail\Message;

/**
 * 
 * @author Alejandro Celaya Alastrué 
 *
 */
class MailServiceMock implements MailServiceInterface
{
	
	private $sendMethodCalled = false;
	private $forceError = false;
    
	public function send() {
	    $this->sendMethodCalled = true;
	    if ($this->forceError)
            return new MailResult(false, "Error!!");
	    else
	        return new MailResult();
	}

	/* (non-PHPdoc)
	 * @see \AcMailer\Service\MailServiceInterface::setBody()
	 */
	public function setBody($body) {
		
	}
	/* (non-PHPdoc)
	 * @see \AcMailer\Service\MailServiceInterface::setSubject()
	 */
	public function setSubject($subject) {
		
	}
	
	public function getMessage() {
	    return new Message();
	}
	
	public function isSendMethodCalled() {
	    return $this->sendMethodCalled;
	}
	
	public function setForceError($forceError) {
	    $this->forceError = $forceError;
	}

}