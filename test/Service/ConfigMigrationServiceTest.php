<?php
namespace AcMailerTest\Service;

use AcMailer\Service\ConfigMigrationService;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class ConfigMigrationServiceTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationServiceTest extends TestCase
{
    /**
     * @var ConfigMigrationService
     */
    protected $service;

    public function setUp()
    {
        $this->service = new ConfigMigrationService();
    }

    public function testParseEmptyConfig()
    {
        $oldConfig = [];
        $expected = [
            'acmailer_options' => [
                'default' => [
                    'message_options' => [
                        'body' => []
                    ],
                    'smtp_options' => [
                        'connection_config' => []
                    ],
                    'file_options' => []
                ]
            ]
        ];
        $this->assertEquals($expected, $this->service->parseConfig($oldConfig));
    }

    public function testParseCompleteConfig()
    {
        $oldConfig = [
            'mail_adapter' => 'Zend\Mail\Transport\Smtp',

            'from' => 'me@acelaya.com',
            'from_name' => 'Alejandro',
            'to' => [],
            'cc' => [],
            'bcc' => [],
            'subject' => 'The subject',
            'body' => 'The body',
            'body_charset' => 'utf-8',
            'template' => [
                'use_template'  => false,
                'path'          => 'ac-mailer/mail-templates/layout',
                'params'        => [],
                'children'      => [
                    'content'   => [
                        'path'   => 'ac-mailer/mail-templates/mail',
                        'params' => [],
                    ]
                ]
            ],
            'attachments' => [
                'files' => [],
                'dir' => [
                    'iterate'   => false,
                    'path'      => 'data/mail/attachments',
                    'recursive' => false,
                ],
            ],

            'server' => 'localhost',
            'smtp_user' => 'me',
            'smtp_password' => 'foobar',
            'ssl' => false,
            'connection_class' => 'login',
            'port' => 25,

            'file_path' => 'data/mail/output',
            'file_callback' => [],
        ];
        $expected = [
            'acmailer_options' => [
                'default' => [
                    'mail_adapter' => 'Zend\Mail\Transport\Smtp',
                    'message_options' => [
                        'from' => 'me@acelaya.com',
                        'from_name' => 'Alejandro',
                        'to' => [],
                        'cc' => [],
                        'bcc' => [],
                        'subject' => 'The subject',
                        'body' => [
                            'content' => 'The body',
                            'charset' => 'utf-8',
                            'use_template'  => false,
                            'template' => [
                                'path'          => 'ac-mailer/mail-templates/layout',
                                'params'        => [],
                                'children'      => [
                                    'content'   => [
                                        'path'   => 'ac-mailer/mail-templates/mail',
                                        'params' => [],
                                    ]
                                ],
                                'default_layout' => []
                            ],
                        ],
                        'attachments' => [
                            'files' => [],
                            'dir' => [
                                'iterate'   => false,
                                'path'      => 'data/mail/attachments',
                                'recursive' => false,
                            ],
                        ],
                    ],
                    'smtp_options' => [
                        'host' => 'localhost',
                        'port' => 25,
                        'connection_class' => 'login',
                        'connection_config' => [
                            'username' => 'me',
                            'password' => 'foobar',
                            'ssl' => false,
                        ]
                    ],
                    'file_options' => [
                        'path' => 'data/mail/output',
                        'callback' => [],
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $this->service->parseConfig($oldConfig));
    }

    public function testParseConfigWithAdapterService()
    {
        $oldConfig = [
            'mail_adapter' => 'Zend\Mail\Transport\Smtp',
            'mail_adapter_service' => 'this_has_preference'
        ];
        $expected = [
            'acmailer_options' => [
                'default' => [
                    'mail_adapter' => 'this_has_preference',
                    'message_options' => [
                        'body' => []
                    ],
                    'smtp_options' => [
                        'connection_config' => []
                    ],
                    'file_options' => []
                ]
            ]
        ];
        $this->assertEquals($expected, $this->service->parseConfig($oldConfig));
    }
}
