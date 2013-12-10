<?php
namespace AcMailer\Service;

use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Exception\RuntimeException;
use AcMailer\Result\ResultInterface;
use AcMailer\Result\MailResult;
use Zend\Mime\Mime;

/**
 * 
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailService implements MailServiceInterface
{
    
    /**
     * @var \Zend\Mail\Message
     */
    private $message;
    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    private $transport;
    /**
     * @var array
     */
    private $attachments = array();
    
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
        // Attach files before sending the email
        if (count($this->attachments) > 0) {
            $mimeMessage = $this->message->getBody();
            if (!$mimeMessage instanceof MimeMessage) {
                $this->setBody(new MimePart($mimeMessage));
                $mimeMessage = $this->message->getBody();
            }
            $bodyContent        = $mimeMessage->generateMessage();
            $bodyPart           = new MimePart($bodyContent);
            $bodyPart->type     = Mime::TYPE_HTML; // TODO
            $attachmentParts    = array();
            $info               = new \finfo(FILEINFO_MIME_TYPE);
            foreach ($this->attachments as $attachment) {
                if (!is_file($attachment)) continue; // If checked file is not valid, continue to the next
                
                $part               = new MimePart(fopen($attachment, 'r'));
                $part->filename     = basename($attachment);
                $part->type         = $info->file($attachment);
                $part->encoding     = Mime::ENCODING_BASE64;
                $part->disposition  = Mime::DISPOSITION_ATTACHMENT;
                $attachmentParts[]  = $part;
            }
            array_unshift($attachmentParts, $bodyPart);
            $body = new MimeMessage();
            $body->setParts($attachmentParts);
            $this->message->setBody($body);
        }
        
        // Send the email
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
        // Is Mime\Message. Set it as the body
        if ($body instanceof MimeMessage)
            $this->message->setBody($body);
        
        // Is a Mime\Part. Wrap it into a Mime\Message
        elseif ($body instanceof MimePart) {
            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts(array($body));
            $this->message->setBody($mimeMessage);
            
        } elseif (is_string($body)) {
            // Is HTML. Create a Mime\Part and wrap it into a Mime\Message
            if (strlen($body) != strlen(strip_tags($body))) {
                $mimePart = new MimePart($body);
                $mimePart->charset  = "utf-8"; // TODO Allow this to be configured by options
                $mimePart->type     = Mime::TYPE_HTML;
                $mimeMessage = new MimeMessage();
                $mimeMessage->setParts(array($mimePart));
                $this->message->setBody($mimeMessage);
            // Is a plain string. Set it as a plain text body
            } else
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
    
	/** 
	 * 
     * @return Returns this MailService for chaining purposes
	 * @see \AcMailer\Service\MailServiceInterface::addAttachment()
	 */
	public function addAttachment($path) {
		$this->attachments[] = $path;
		return $this;
	}

	/** 
	 * 
     * @return Returns this MailService for chaining purposes
	 * @see \AcMailer\Service\MailServiceInterface::addAttachments()
	 */
	public function addAttachments(array $paths) {
		$this->attachments = array_merge($this->attachments, $paths);
		return $this;
	}

	/** 
	 * 
     * @return Returns this MailService for chaining purposes
	 * @see \AcMailer\Service\MailServiceInterface::setAttachments()
	 */
	public function setAttachments(array $paths) {
		$this->attachments = $paths;
		return $this;
	}
    
}