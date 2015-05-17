<?php
return [

    'service_manager' => [
        'abstract_factories' => [
            'AcMailer\Service\Factory\MailServiceAbstractFactory',
            'AcMailer\Options\Factory\MailOptionsAbstractFactory'
        ],
        'aliases' => [
            'mailservice' => 'AcMailer\Service\MailService',
            'AcMailer\Service\MailService' => 'acmailer.mail_service.default',
            'AcMailer\Options\MailOptions' => 'acmailer.mailoptions.default',
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
