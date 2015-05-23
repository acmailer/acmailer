<?php
return [

    'service_manager' => [
        'invokables' => [
            'AcMailer\Service\ConfigMigrationService' => 'AcMailer\Service\ConfigMigrationService'
        ],
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

    'controllers' => [
        'factories' => [
            'AcMailer\Controller\ConfigMigration' => 'AcMailer\Controller\Factory\ConfigMigrationControllerFactory'
        ]
    ],

    'console' => [
        'router' => [
            'routes' => [
                'acmailer-parse-config' => [
                    'options' => [
                        'route' => 'acmailer parse-config [--configKey=] [--format=(php|xml|ini|json)] [--outputFile=]',
                        'defaults' => [
                            'controller' => 'AcMailer\Controller\ConfigMigration',
                            'action'     => 'parse-config'
                        ]
                    ]
                ]
            ]
        ]
    ],

];
