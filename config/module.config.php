<?php
return array(
    
    'service_manager' => array(
        'factories' => array(
            'AcMailer\Service\MailService'  => 'AcMailer\Service\Factory\MailServiceFactory',
        ),
    ),
    
);
