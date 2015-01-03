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
    const ERROR_MESSAGE = 'This is a forced exception';
    
    /**
     * @var boolean
     */
    private $forceError = false;
    /**
     * @var \Exception
     */
    private $exeption = null;
    
    public function send(Message $message)
    {
        if ($this->forceError) {
            throw $this->exeption;
        }
    }
    
    /**
     * If force error is set to true, the method send will throw a RuntimeException when is called
     * @param boolean $forceError
     * @param \Exception|null $exception
     */
    public function setForceError($forceError, $exception = null)
    {
        $this->forceError = $forceError;
        $this->exeption = $exception instanceof \Exception
            ? $exception
            : new RuntimeException(self::ERROR_MESSAGE, -1);
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
