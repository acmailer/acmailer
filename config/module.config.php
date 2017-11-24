<?php

namespace AcMailer;

use AcMailer\Service\MailService;

return [

    'service_manager' => [
        'abstract_factories' => [
            Service\Factory\MailServiceAbstractFactory::class,
            Options\Factory\MailOptionsAbstractFactory::class,
        ],
        'aliases' => [
            'mailservice' => 'acmailer.mailservice.default',
            MailService::class => 'acmailer.mailservice.default',
            Options\MailOptions::class => 'acmailer.mailoptions.default',
            'mailviewrenderer' => 'viewrenderer',
        ],
    ],

    'controller_plugins' => [
        'abstract_factories' => [
            Controller\Plugin\Factory\SendMailPluginAbstractFactory::class,
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

];
