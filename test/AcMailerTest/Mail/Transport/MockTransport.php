<?php
namespace AcMailerTest\Mail\Transport;

use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Exception\RuntimeException;

/**
 * Mocks mail transport
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MockTransport implements TransportInterface
{
    
    const ERROR_MESSAGE = "This is a forced exception";
    
    /**
     * @var boolean
     */
    private $forceError = false;
    
    public function send(Message $message)
    {
        if ($this->forceError)
            throw new RuntimeException(self::ERROR_MESSAGE, -1);
    }
    
    /**
     * If force error is set to true, the method send will throw a RuntimeException when is called
     * @param boolean $forceError
     */
    public function setForceError($forceError)
    {
        $this->forceError = $forceError;
    }
    /**
     * Tells if a RuntimeException will be thrown when the method send is called
     * @return boolean
     */
    public function isForceError()
    {
        return $this->forceError;
    }
    
}