<?php

namespace AcMailer;

return [

    'service_manager' => [
        'invokables' => [
            Service\ConfigMigrationService::class => Service\ConfigMigrationService::class,
        ],
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

    'controllers' => [
        'factories' => [
            Controller\ConfigMigration::class => Controller\Factory\ConfigMigrationControllerFactory::class,
        ]
    ],

    'console' => [
        'router' => [
            'routes' => [
                'acmailer-parse-config' => [
                    'options' => [
                        'route' => 'acmailer parse-config [--configKey=] [--format=(php|xml|ini|json)] [--outputFile=]',
                        'defaults' => [
                            'controller' => Controller\ConfigMigration::class,
                            'action'     => 'parse-config'
                        ]
                    ]
                ]
            ]
        ]
    ],

];
