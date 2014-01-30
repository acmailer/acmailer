<?php
return array(
    
    'service_manager' => array(
        'factories' => array(
            'AcMailer\Service\MailService'	=> 'AcMailer\Service\Factory\MailServiceFactory',
            'AcMailer\Options\MailOptions' 	=> 'AcMailer\Options\Factory\MailOptionsFactory'
        ),
    ),

    'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
    ),

);
