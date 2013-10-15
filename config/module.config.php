<?php
return array(
    
    'service_manager' => array(
        'factories' => array(
            'MailService'   => 'AcMailer\Service\Factory\MailServiceFactory',
        ),
    ),
    
);
