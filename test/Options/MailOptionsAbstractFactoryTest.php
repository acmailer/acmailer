<?php
namespace AcMailerTest\Options;

use AcMailer\Options\Factory\MailOptionsAbstractFactory;
use AcMailer\Options\MailOptions;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use Zend\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MailOptionsFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsAbstractFactoryTest extends TestCase
{
    /**
     * @var MailOptionsAbstractFactory
     */
    private $mailOptionsFactory;
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    public function setUp()
    {
        $this->mailOptionsFactory = new MailOptionsAbstractFactory();
    }

    public function testCanCreateServiceWithName()
    {
        $this->initServiceManager();
        $this->assertTrue($this->mailOptionsFactory->canCreateServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.default',
            ''
        ));
        $this->assertFalse($this->mailOptionsFactory->canCreateServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.employees',
            ''
        ));
        $this->assertFalse($this->mailOptionsFactory->canCreateServiceWithName($this->serviceLocator, 'foo', ''));
        $this->assertFalse($this->mailOptionsFactory->canCreateServiceWithName(
            $this->serviceLocator,
            'invalid.mailoptions.foobar',
            ''
        ));
        $this->assertFalse($this->mailOptionsFactory->canCreateServiceWithName(
            new ServiceManagerMock(['Config' => []]),
            'acmailer.mailoptions.default',
            ''
        ));
    }

    public function testSomeCustomOptions()
    {
        $services = $this->initServiceManager();
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.default',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals(
            [$services['Config']['acmailer_options']['default']['message_options']['to']],
            $mailOptions->getMessageOptions()->getTo()
        );
        $this->assertEquals(
            $services['Config']['acmailer_options']['default']['message_options']['from'],
            $mailOptions->getMessageOptions()->getFrom()
        );
        $this->assertEquals([], $mailOptions->getMessageOptions()->getCc());
        $this->assertEquals([], $mailOptions->getMessageOptions()->getBcc());
    }

    public function testOldConfigKey()
    {
        $services = $this->initServiceManager('mail_options');
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.default',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals(
            [$services['Config']['mail_options']['default']['message_options']['to']],
            $mailOptions->getMessageOptions()->getTo()
        );
        $this->assertEquals(
            $services['Config']['mail_options']['default']['message_options']['from'],
            $mailOptions->getMessageOptions()->getFrom()
        );
        $this->assertEquals([], $mailOptions->getMessageOptions()->getCc());
        $this->assertEquals([], $mailOptions->getMessageOptions()->getBcc());
    }

    public function testCreateServiceWithNonarrayOptions()
    {
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            new ServiceManagerMock([
                'Config' => [
                    'acmailer_options' => [
                        'invalid' => ''
                    ]
                ]
            ]),
            'acmailer.mailoptions.invalid',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
    }

    public function testExtendOptions()
    {
        $this->serviceLocator = new ServiceManagerMock([
            'Config' => [
                'acmailer_options' => [
                    'default' => [
                        'message_options' => [
                            'to'    => 'foo@bar.com',
                            'from'  => 'Me',
                        ]
                    ],
                    'another' => [
                        'extends' => 'default'
                    ]
                ]
            ]
        ]);

        /** @var MailOptions $mailOptions */
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.another',
            ''
        );
        $this->assertEquals(['foo@bar.com'], $mailOptions->getMessageOptions()->getTo());
        $this->assertEquals('Me', $mailOptions->getMessageOptions()->getFrom());
    }

    public function testExtendWithValueNullIsIgnored()
    {
        $this->serviceLocator = new ServiceManagerMock([
            'Config' => [
                'acmailer_options' => [
                    'default' => [
                        'extends' => null,
                        'message_options' => [
                            'to'    => 'foo@bar.com',
                            'from'  => 'Me',
                        ]
                    ],
                ]
            ]
        ]);

        /** @var MailOptions $mailOptions */
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.default',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
    }

    public function testExtendsSingleChaining()
    {
        $this->serviceLocator = new ServiceManagerMock([
            'Config' => [
                'acmailer_options' => [
                    'default' => [
                        'extends' => null,
                        'message_options' => [
                            'to'    => 'foo@bar.com'
                        ]
                    ],
                    'foo' => [
                        'extends' => 'default',
                        'message_options' => [
                            'from' => 'foo@bar.com'
                        ]
                    ]
                ]
            ]
        ]);

        /** @var MailOptions $mailOptions */
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.foo',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals(
            [
                'to'   => [['foo@bar.com']],
                'from' => 'foo@bar.com',
            ],
            [
                'to' => [$mailOptions->getMessageOptions()->getTo()],
                'from' => $mailOptions->getMessageOptions()->getFrom(),
            ]
        );
    }

    public function testExtendsDoubleChaining()
    {
        $this->serviceLocator = new ServiceManagerMock([
            'Config' => [
                'acmailer_options' => [
                    'default' => [
                        'extends' => null,
                        'message_options' => [
                            'to'    => 'foo@bar.com',
                        ]
                    ],
                    'foo' => [
                        'extends' => 'default',
                        'message_options' => [
                            'from' => 'foo@bar.com'
                        ]
                    ],
                    'bar' => [
                        'extends' => 'foo',
                        'message_options' => [
                            'to' => 'noone@here.com',
                            'subject' => 'Foobar subject'
                        ]
                    ]
                ]
            ]
        ]);

        /** @var MailOptions $mailOptions */
        $mailOptions = $this->mailOptionsFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailoptions.bar',
            ''
        );
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals(
            [
                'to' => [['noone@here.com']],
                'from' => 'foo@bar.com',
                'subject' => 'Foobar subject'
            ],
            [
                'to' => [$mailOptions->getMessageOptions()->getTo()],
                'from' => $mailOptions->getMessageOptions()->getFrom(),
                'subject' => $mailOptions->getMessageOptions()->getSubject()
            ]
        );
    }

    protected function initServiceManager($mailConfigKey = 'acmailer_options', $serviceName = 'default')
    {
        $services = [
            'Config' => [
                $mailConfigKey => [
                    $serviceName => [
                        'message_options' => [
                            'to'    => 'foo@bar.com',
                            'from'  => 'Me',
                        ]
                    ]
                ]
            ]
        ];
        $this->serviceLocator = new ServiceManagerMock($services);
        return $services;
    }
}
