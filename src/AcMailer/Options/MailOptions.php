<?php
namespace AcMailer\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Mail\Transport\TransportInterface;

/**
 * Module options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptions extends AbstractOptions
{
    
    /**
     * @var string
     */
    protected $mailAdapter = 'Zend\Mail\Transport\Sendmail';
    /**
     * @var string
     */
    protected $server = 'localhost';
    /**
     * @var string
     */
    protected $from = '';
    /**
     * @var string
     */
    protected $fromName = '';
    /**
     * @var array
     */
    protected $to = array();
    /**
     * @var array
     */
    protected $cc = array();
    /**
     * @var array
     */
    protected $bcc = array();
    /**
     * @var string
     */
    protected $smtpUser = '';
    /**
     * @var string
     */
    protected $smtpPassword = '';
    /**
     * @var string
     */
    protected $body = '';
    /**
     * @var string
     */
    protected $subject = '';
    
//     protected $attachmentsDir = 'data/mail/attachments';
    /**
     * @var int
     */
    protected $port = 25;
    
	/**
	 * @return TransportInterface the $mailAdapter
	 */
	public function getMailAdapter() {
	    if (!$this->mailAdapter instanceof TransportInterface)
	        $this->mailAdapter = new $this->mailAdapter();
	    
		return $this->mailAdapter;
	}
	/**
	 * @param TransportInterface $mailAdapter
	 * @return MailOptions
	 */
	public function setMailAdapter(TransportInterface $mailAdapter) {
		$this->mailAdapter = $mailAdapter;
		return $this;
	}

	/**
	 * @return string $server
	 */
	public function getServer() {
		return $this->server;
	}
	/**
	 * @param string $server
	 * @return MailOptions
	 */
	public function setServer($server) {
		$this->server = $server;
		return $this;
	}

	/**
	 * @return string $from
	 */
	public function getFrom() {
		return $this->from;
	}
	/**
	 * @param string $from
	 * @return MailOptions
	 */
	public function setFrom($from) {
		$this->from = $from;
		return $this;
	}

	/**
	 * @return string $fromName
	 */
	public function getFromName() {
		return $this->fromName;
	}
	/**
	 * @param string $fromName
	 */
	public function setFromName($fromName) {
		$this->fromName = $fromName;
		
	}

	/**
	 * @return array $to
	 */
	public function getTo() {
	    if (is_string($this->to))
	        $this->to = array($this->to);
	    
		return $this->to;
	}
	/**
	 * @param array $to
	 * @return MailOptions
	 */
	public function setTo($to) {
		$this->to = $to;
		return $this;
	}
	
	/**
	 * @return array $cc
	 */
	public function getCc() {
	    if (is_string($this->cc))
	        $this->cc = array($this->cc);
	    
		return $this->cc;
	}
	/**
	 * @param array $cc
	 * @return MailOptions
	 */
	public function setCc($cc) {
		$this->cc = $cc;
		return $this;
	}
	
	/**
	 * @return array $bcc
	 */
	public function getBcc() {
	    if (is_string($this->bcc))
	        $this->bcc = array($this->bcc);
	    
		return $this->bcc;
	}
	/**
	 * @param array $bcc
	 * @return MailOptions
	 */
	public function setBcc($bcc) {
		$this->bcc = $bcc;
		return $this;
	}

	/**
	 * @return string $smtpUser
	 */
	public function getSmtpUser() {
		return $this->smtpUser;
	}
	/**
	 * @param string $smtpUser
	 * @return MailOptions
	 */
	public function setSmtpUser($smtpUser) {
		$this->smtpUser = $smtpUser;
		return $this;
	}

	/**
	 * @return string $smtpPassword
	 */
	public function getSmtpPassword() {
		return $this->smtpPassword;
	}
	/**
	 * @param string $smtpPassword
	 * @return MailOptions
	 */
	public function setSmtpPassword($smtpPassword) {
		$this->smtpPassword = $smtpPassword;
		return $this;
	}

	/**
	 * @return the $body
	 */
	public function getBody() {
		return $this->body;
	}
	/**
	 * @param string $body
	 * @return MailOptions
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return string $subject
	 */
	public function getSubject() {
		return $this->subject;
	}
	/**
	 * @param string $subject
	 * @return MailOptions
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return int $port
	 */
	public function getPort() {
		return (int) $this->port;
	}
	/**
	 * @param int $port
	 * @return MailOptions
	 */
	public function setPort($port) {
		$this->port = (int) $port;
		return $this;
	}
	
}