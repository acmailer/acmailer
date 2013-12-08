<?php
namespace AcMailer\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use AcMailer\Service\MailService;
use AcMailer\Options\MailOptions;

/**
 * Constructs a new MailService injecting on it a Message and Transport object constructed with mail options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceFactory implements FactoryInterface
{
	
    /**
     * @var MailOptions
     */
    private $mailOptions;
    
	public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sm) {
	    $this->mailOptions = $sm->get('AcMailer\Option\MailOptions');
	    
	    // Prepare Mail Message
	    $message = new Message();
	    $message->setSubject($this->mailOptions->getSubject())
        	    ->setFrom($this->mailOptions->getFrom(), $this->mailOptions->getFromName())
        	    ->setTo($this->mailOptions->getTo())
	            ->setCc($this->mailOptions->getCc())
	            ->setBcc($this->mailOptions->getBcc());
	    
	    // Prepare Mail Transport
	    $transport = $this->mailOptions->getMailAdapter();
	    if ($transport instanceof Smtp) {
	    	$transport->setOptions(new SmtpOptions(array(
    			'host'              => $this->mailOptions->getServer(),
    			'port'              => $this->mailOptions->getPort(),
    			'connection_class'  => 'login',
    			'connection_config' => array(
					'username' => $this->mailOptions->getSmtpUser(),
					'password' => $this->mailOptions->getSmtpPassword(),
    			),
	    	)));
	    }
	    
	    // Prepare MailService
	    $mailService = new MailService($message, $transport);
	    $mailService->setSubject($this->mailOptions->getSubject())
	                ->setBody($this->mailOptions->getBody());
	    return $mailService;
	}
    
}