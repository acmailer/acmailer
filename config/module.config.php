<?php
declare(strict_types=1);

namespace AcMailer;

return [

    'acmailer_options' => [
        'emails' => [],

        'mail_services' => [
            'default' => [],
        ],
    ],

    'service_manager' => [
        'factories' => [
            Model\EmailBuilder::class => Model\EmailBuilderFactory::class,
            'acmailer.mailservice.default' => Service\Factory\MailServiceAbstractFactory::class,
            View\MailViewRendererFactory::SERVICE_NAME => View\MailViewRendererFactory::class,
        ],

        'abstract_factories' => [
            Service\Factory\MailServiceAbstractFactory::class,
        ],

        'aliases' => [
            Service\MailServiceInterface::class => 'acmailer.mailservice.default',
            Service\MailService::class => 'acmailer.mailservice.default',
            'mailservice' => 'acmailer.mailservice.default',

            'mailviewrenderer' => 'ViewRenderer',
        ],
    ],

];
