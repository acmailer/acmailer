<?php
return [

    'service_manager' => [
        'abstract_factories' => [
            'AcMailer\Service\Factory\MailServiceAbstractFactory',
            'AcMailer\Options\Factory\MailOptionsAbstractFactory'
        ],
        'aliases' => [
            'mailservice' => 'acmailer.mailservice.default',
            'AcMailer\Service\MailService' => 'acmailer.mailservice.default',
            'AcMailer\Options\MailOptions' => 'acmailer.mailoptions.default',
            'mailviewrenderer' => 'viewrenderer'
        ]
    ],

    'controller_plugins' => [
        'abstract_factories' => [
            'AcMailer\Controller\Plugin\Factory\SendMailPluginAbstractFactory'
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

];
