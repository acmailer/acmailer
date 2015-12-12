<?php
namespace AcMailerTest\Service;

use AcMailer\Event\MailEvent;
use AcMailer\Options\MailOptions;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\Factory\MailServiceFactory;
use AcMailerTest\Event\MailListenerMock;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MailServiceFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceAbstractFactoryTest extends TestCase
{
    /**
     * @var MailServiceAbstractFactory
     */
    private $mailServiceFactory;
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    public function setUp()
    {
        $this->mailServiceFactory = new MailServiceAbstractFactory();
    }

    public function testMessageData()
    {
        $options = [
            'message_options' => [
                'from'          => 'alejandro@alejandrocelaya.com',
                'from_name'     => 'Alejandro Celaya',
                'reply_to'      => 'alejandro@alejandrocelaya.com',
                'reply_to_name' => 'Alejandro Celaya',
                'to'            => ['foo@bar.com', 'bar@foo.com'],
                'cc'            => ['account@domain.com'],
                'bcc'           => ['hidden@domain.com'],
                'subject'       => 'The subject',
                'body'          => ['content' => 'The body'],
            ]
        ];
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );

        $this->assertInstanceOf('AcMailer\Service\MailService', $mailService);
        $this->assertEquals(
            $options['message_options']['from_name'],
            $mailService->getMessage()->getFrom()->get($options['message_options']['from'])->getName()
        );
        $this->assertEquals(
            $options['message_options']['reply_to_name'],
            $mailService->getMessage()->getReplyTo()->get($options['message_options']['reply_to'])->getName()
        );
        $toArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getTo()));
        $ccArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getCc()));
        $bccArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getBcc()));
        $this->assertEquals($options['message_options']['to'], $toArray);
        $this->assertEquals($options['message_options']['cc'], $ccArray);
        $this->assertEquals($options['message_options']['bcc'], $bccArray);
        $this->assertEquals($options['message_options']['subject'], $mailService->getMessage()->getSubject());
        $this->assertInstanceof('Zend\Mime\Message', $mailService->getMessage()->getBody());
    }

    public function testSmtpAdapter()
    {
        $options = [
            'mail_adapter' => 'Zend\Mail\Transport\Smtp',
            'smtp_options' => [
                'host'  => 'the.host',
                'port'  => 465,
                'connection_config' => [
                    'username'  => 'alejandro',
                    'password'  => '1234',
                    'ssl'       => 'ssl',
                ]
            ]
        ];
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );

        /* @var Smtp $transport */
        $transport = $mailService->getTransport();
        $this->assertInstanceOf($options['mail_adapter'], $transport);
        $connConfig = $transport->getOptions()->getConnectionConfig();
        $this->assertEquals($options['smtp_options']['connection_config']['username'], $connConfig['username']);
        $this->assertEquals($options['smtp_options']['connection_config']['password'], $connConfig['password']);
        $this->assertEquals($options['smtp_options']['connection_config']['ssl'], $connConfig['ssl']);
        $this->assertEquals($options['smtp_options']['host'], $transport->getOptions()->getHost());
        $this->assertEquals($options['smtp_options']['port'], $transport->getOptions()->getPort());
    }

    public function testFileAdapter()
    {
        $options = [
            'mail_adapter'  => 'file',
            'file_options' => [
                'path'     => __DIR__,
                'callback' => function ($transport) {
                    return get_class($transport);
                }
            ]
        ];
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );

        /* @var File $transport */
        $transport = $mailService->getTransport();
        $this->assertInstanceOf('Zend\Mail\Transport\File', $transport);
        $this->assertEquals($options['file_options']['path'], $transport->getOptions()->getPath());
        $this->assertEquals($options['file_options']['callback'], $transport->getOptions()->getCallback());
    }

    public function testAdapterAsService()
    {
        $this->initServiceLocator([
            'mail_adapter' => 'my_transport_service'
        ]);
        $transport = new Sendmail();
        $this->serviceLocator->set('my_transport_service', $transport);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        $this->assertSame($transport, $mailService->getTransport());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testAdapterAsInvalidService()
    {
        $this->initServiceLocator([
            'mail_adapter' => 'my_transport_service'
        ]);
        $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testAdapterAsAserviceNotReturningTransport()
    {
        $this->initServiceLocator([
            'mail_adapter' => 'my_transport_service'
        ]);
        $this->serviceLocator->set('my_transport_service', new \stdClass());
        $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
    }

    public function testAdapterAsInstance()
    {
        $expected = new Sendmail();
        $this->initServiceLocator([
            'mail_adapter' => $expected
        ]);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        $this->assertSame($expected, $mailService->getTransport());
    }

    public function testViewRendererService()
    {
        $this->initServiceLocator();
        // Create the service with default configuration
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        /** @var PhpRenderer $renderer */
        $renderer = $mailService->getRenderer();
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
        $this->assertInstanceOf('Zend\View\Resolver\TemplatePathStack', $renderer->resolver());

        // Set a template_map and unset the template_path_stack
        $config = $this->serviceLocator->get('Config');
        unset($config['view_manager']['template_path_stack']);
        $config['view_manager']['template_map'] = [];
        $this->serviceLocator->set('Config', $config);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        /** @var PhpRenderer $renderer */
        $renderer = $mailService->getRenderer();
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
        $this->assertInstanceOf('Zend\View\Resolver\TemplateMapResolver', $renderer->resolver());

        // Set both a template_map and a template_path_stack
        $this->initServiceLocator();
        $config = $this->serviceLocator->get('Config');
        $config['view_manager']['template_map'] = [];
        $this->serviceLocator->set('Config', $config);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        /** @var PhpRenderer $renderer */
        $renderer = $mailService->getRenderer();
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
        $this->assertInstanceOf('Zend\View\Resolver\AggregateResolver', $renderer->resolver());

        // Set a viewrenderer service and see if it is used
        $renderer = new PhpRenderer();
        $this->serviceLocator->set('mailviewrenderer', $renderer);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        $this->assertSame($renderer, $mailService->getRenderer());
    }

    public function testTemplateBody()
    {
        $options = [
            'message_options' => [
                'body' => [
                    'content' => 'This body is not going to be used',
                    'use_template'  => true,
                    'template' => [
                        'path'          => 'ac-mailer/mail-templates/layout',
                        'children'      => [
                            'content'   => [
                                'path'   => 'ac-mailer/mail-templates/mail',
                            ]
                        ]
                    ],
                ]
            ]
        ];
        $this->initServiceLocator($options);

        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../view');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);
        $this->serviceLocator->set('mailviewrenderer', $renderer);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );

        $this->assertNotEquals($options ['message_options']['body']['content'], $mailService->getMessage()->getBody());
        $this->assertInstanceOf('Zend\Mime\Message', $mailService->getMessage()->getBody());
    }

    public function testWithDefaultLayout()
    {
        $options = [
            'message_options' => [
                'body' => [
                    'use_template'  => true,
                    'template' => [
                        'path'          => 'ac-mailer/mail-templates/mail',
                        'default_layout' => [
                            'path' => 'ac-mailer/mail-templates/layout',
                        ]
                    ],
                ]
            ]
        ];
        $this->initServiceLocator($options);

        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../view');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);
        $this->serviceLocator->set('mailviewrenderer', $renderer);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        $this->assertInstanceOf('Zend\Mime\Message', $mailService->getMessage()->getBody());
    }

    public function testFileAttachments()
    {
        $cwd = getcwd();
        chdir(dirname(__DIR__));
        $options = [
            'message_options' => [
                'attachments' => [
                    'files' => [
                        'attachments/file1',
                        'attachments/file2',
                    ],
                    'dir' => [
                        'iterate'   => true,
                        'path'      => 'attachments/dir',
                        'recursive' => true,
                    ],
                ],
            ]
        ];
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );

        $this->assertCount(4, $mailService->getAttachments());
        chdir($cwd);
    }

    public function testListeners()
    {
        $options = [
            'mail_listeners' => [
                new MailListenerMock(),
                'mail_listener_service',
                'AcMailerTest\Event\MailListenerMock'
            ]
        ];
        $this->initServiceLocator($options);
        $this->serviceLocator->set('mail_listener_service', new MailListenerMock());

        /** @var EventManagerAwareInterface $mailService */
        $mailService = $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
        $this->assertCount(3, $mailService->getEventManager()->getListeners(MailEvent::EVENT_MAIL_PRE_SEND));
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testInvalidListenersThrowException()
    {
        $options = [
            'mail_listeners' => [
                new \stdClass(),
                'invalid_service',
                '\Nonsens\Foo'
            ]
        ];
        $this->initServiceLocator($options);
        $this->mailServiceFactory->createServiceWithName(
            $this->serviceLocator,
            'acmailer.mailservice.default',
            ''
        );
    }

    private function initServiceLocator(array $mailOptions = [])
    {
        $this->serviceLocator = new ServiceManagerMock([
            'acmailer.mailoptions.default' => new MailOptions($mailOptions),
            'Config' => include __DIR__ . '/../../config/module.config.php'
        ]);
    }
}
