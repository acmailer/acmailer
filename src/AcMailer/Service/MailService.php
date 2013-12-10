<?php
namespace AcMailer\Service;

use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Exception\RuntimeException;
use AcMailer\Result\ResultInterface;
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
     * @return ResultInterface
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
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body Email body
     * @return Returns this MailService for chaining purposes
     * @see \AcMailer\Service\MailServiceInterface::setBody()
     */
    public function setBody($body) {
        if ($body instanceof MimeMessage)                       // Is Mime\Message. Set it as the body
            $this->message->setBody($body);
        elseif ($body instanceof MimePart) {                    // Is a Mime\Part. Wrap it into a Mime\Message
            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts(array($body));
            $this->message->setBody($mimeMessage);
        } elseif (is_string($body)) {
            if (strlen($body) != strlen(strip_tags($body))) {   // Is HTML. Create a Mime\Part and wrap it into a Mime\Message
                $mimePart = new MimePart($body);
                $mimePart->charset  = "utf-8";
                $mimePart->type     = "text/html";
                $mimeMessage = new MimeMessage();
                $mimeMessage->setParts(array($mimePart));
                $this->message->setBody($mimeMessage);
            } else                                              // Is a plain string. Set it as a plain text body
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