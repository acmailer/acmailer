<?php
namespace AcMailer\Service;

use Zend\Mail\Transport\TransportInterface;
use \Zend\Mail\Message;
use \Zend\Mail\Transport\Exception\RuntimeException;
use AcMailer\Result\MailResult;

/**
 * 
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailService implements MailServiceInterface
{
    
    /**
     * 
     * @var \Zend\Mail\Message
     */
    private $message;
    /**
     * 
     * @var Zend\Mail\Transport\TransportInterface
     */
    private $transport;
    
    /**
     * Creates a new MailService
     * @param Message $message
     * @param TransportInterface $transport
     */
    public function __construct(Message $message, TransportInterface $transport) {
        $this->message      = $message;
        $this->transport    = $transport;
    }
    
    /**
     * Returns this service's message
     * @return \Zend\Mail\Message
     * @see \AcMailer\Service\MailServiceInterface::getMessage()
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * Sends the mail and
     * @return \AcMailer\Entity\MailResult
     * @see \AcMailer\Service\MailServiceInterface::send()
     */
    public function send() {        
        try {
            $this->transport->send($this->message);
            return new MailResult();
        } catch (RuntimeException $e) {
            return new MailResult(false, $e->getMessage());
        }
    }
    
    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body Message body
     * @return Returns this MailService for chaining purposes
     * @see \AcMailer\Service\MailServiceInterface::setBody()
     */
    public function setBody($body) {
        if ($body instanceof \Zend\Mime\Part) {
            $aux = new \Zend\Mime\Message();
            $aux->setParts(array($body));
            $this->message->setBody($aux);
        } else {
            $this->message->setBody($body);
        }
        return $this;
    }
    /**
     * Sets the message subject
     * @param $subject The subject of the message
     * @return Returns this MailService for chaining purposes
     * @see \AcMailer\Service\MailServiceInterface::setSubject()
     */
    public function setSubject($subject) {
        $this->message->setSubject($subject);
        return $this;
    }
    
}