<?php
namespace AcMailer\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use AcMailer\Service\MailService;
use AcMailer\Options\MailOptions;
use Zend\Debug\Debug;

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
	    $this->mailOptions = $sm->get('AcMailer\Options\MailOptions');
	    
	    // Prepare Mail Message
	    $message = new Message();
	    $message->setFrom($this->mailOptions->getFrom(), $this->mailOptions->getFromName())
        	    ->setTo($this->mailOptions->getTo())
	            ->setCc($this->mailOptions->getCc())
	            ->setBcc($this->mailOptions->getBcc());
	    
	    // Prepare Mail Transport
	    $transport = $this->mailOptions->getMailAdapter();
	    if ($transport instanceof Smtp) {
	        $connConfig = array(
	            'username' => $this->mailOptions->getSmtpUser(),
	            'password' => $this->mailOptions->getSmtpPassword(),
	        );
	        
	        // Check if SSL should be used
	        if ($this->mailOptions->getSsl() !== false)
	            $connConfig['ssl'] = $this->mailOptions->getSsl();
	        
	        // Set SMTP transport options
	    	$transport->setOptions(new SmtpOptions(array(
    			'host'              => $this->mailOptions->getServer(),
    			'port'              => $this->mailOptions->getPort(),
    			'connection_class'  => 'login',
    			'connection_config' => $connConfig,
	    	)));
	    }
	    
	    // Prepare MailService
	    $mailService = new MailService($message, $transport, $sm->get('viewrenderer'));
	    $mailService->setSubject($this->mailOptions->getSubject());
	    
	    // Set body, either by using a template or the body option
	    $template = $this->mailOptions->getTemplate();
	    if ($template['use_template'] === true)
	        $mailService->setTemplate($template['path'], $template['params']);
	    else
	        $mailService->setBody($this->mailOptions->getBody());
	    
	    // Attach files
	    $dir = $this->mailOptions->getAttachmentsDir();
	    if (is_string($dir) && is_dir($dir)) {
	        $files = new \RecursiveIteratorIterator(
        		new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
        		\RecursiveIteratorIterator::CHILD_FIRST
	        );
	        
	        foreach ($files as $fileInfo) {
	            if ($fileInfo->isDir()) continue;
	            $mailService->addAttachment($fileInfo->getPathname());
	        }
	    }
	    
	    return $mailService;
	}
    
}