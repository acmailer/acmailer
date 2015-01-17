<?php
return [

    'service_manager' => [
        'factories' => [
            'AcMailer\Service\MailService'	=> 'AcMailer\Service\Factory\MailServiceFactory',
            'AcMailer\Options\MailOptions' 	=> 'AcMailer\Options\Factory\MailOptionsFactory'
        ],
        'aliases' => [
            'mailservice' => 'AcMailer\Service\MailService',
            'mailviewrenderer' => 'viewrenderer'
        ]
    ],

    'controller_plugins' => [
        'factories' => [
            'sendMail' => 'AcMailer\Controller\Plugin\Factory\SendMailPluginFactory'
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

];
