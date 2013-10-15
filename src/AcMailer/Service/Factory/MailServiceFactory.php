<?php
namespace AcMailer\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use AcMailer\Service\MailService;

/**
 * Constructs a new MailService injecting on it a Message and Transport object constructed with configuration params
 * @author Alejandro Celaya Alastrué
 *
 */
class MailServiceFactory implements FactoryInterface
{
	
	public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sm) {
	    $config        = $sm->get('Config');
	    if (!isset($config['mailParams']))
	        throw new \Exception("Mail configuration has not been provided. Include a mailParams section in one of your configuration files");
	    
	    $mailParams    = $config['mailParams'];
	    $message       = new Message();
	    $message->setSubject($mailParams['subject'])
        	    ->setFrom($mailParams['from'], $mailParams['fromName'])
        	    ->setTo($mailParams['to']);
	    
	    $transport = new $mailParams['mailAdapter']();
	    if ($transport instanceof Smtp) {
	    	$transport->setOptions(new SmtpOptions(array(
    			'host'              => $mailParams['server'],
    			'port'              => $mailParams['port'],
    			'connection_class'  => 'login',
    			'connection_config' => array(
					'username' => $mailParams['from'],
					'password' => $mailParams['password'],
    			),
	    	)));
	    }
	    
	    $mailService = new MailService($message, $transport);
	    $mailService->setSubject($mailParams['subject']);
	    return $mailService;
	}
    
}