<?php
use Zend\ServiceManager\ServiceLocatorInterface;
use AcMailer\Options\MailOptions;

return array(
    
    'service_manager' => array(
        'factories' => array(
            'AcMailer\Service\MailService'  => 'AcMailer\Service\Factory\MailServiceFactory',
            'AcMailer\Options\MailOptions' => function(ServiceLocatorInterface $sm) {
                $config = $sm->get('Config');
                return new MailOptions(isset($config['mail_options']) ? $config['mail_options'] : array());
            }
        ),
    ),
    
    'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
    ),
    
);
