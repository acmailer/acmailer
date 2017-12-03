<?php
declare(strict_types=1);

namespace AcMailer;

use AcMailer\Model;

return [

    'service_manager' => [
        'factories' => [
            Model\EmailBuilder::class => Model\EmailBuilderFactory::class,
            'acmailer.mailservice.default' => Service\Factory\MailServiceAbstractFactory::class,
        ],

        'abstract_factories' => [
            Service\Factory\MailServiceAbstractFactory::class,
        ],

        'aliases' => [
            Service\MailServiceInterface::class => 'acmailer.mailservice.default',
            Service\MailService::class => 'acmailer.mailservice.default',
            'mailservice' => 'acmailer.mailservice.default',

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
