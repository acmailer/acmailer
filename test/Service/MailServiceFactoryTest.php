<?php
namespace AcMailerTest\Service;

use AcMailer\Options\MailOptions;
use AcMailer\Service\Factory\MailServiceFactory;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Class MailServiceFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailServiceFactory
     */
    private $mailServiceFactory;
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    public function setUp()
    {
        $this->mailServiceFactory = new MailServiceFactory();
    }

    public function testServiceIsCreated()
    {
        $this->initServiceLocator();
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('AcMailer\Service\MailService', $mailService);
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testExceptionisThrownIfOptionsServiceDoesNotExist()
    {
        $this->mailServiceFactory->createService(new ServiceManagerMock());
    }

    public function testMessageData()
    {
        $options = array(
            'from'      => 'Alejandro Celaya',
            'from_name' => 'alejandro@alejandrocelaya.com',
            'to'        => array('foo@bar.com', 'bar@foo.com'),
            'cc'        => array('account@domain.com'),
            'bcc'       => array(),
            'subject'   => 'The subject',
            'body'      => 'The body',
        );
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);

        $this->assertInstanceOf('AcMailer\Service\MailService', $mailService);
        $this->assertEquals(
            $options['from_name'],
            $mailService->getMessage()->getFrom()->get($options['from'])->getName()
        );
        $toArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getTo()));
        $ccArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getCc()));
        $bccArray = array_keys(ArrayUtils::iteratorToArray($mailService->getMessage()->getBcc()));
        $this->assertEquals($options['to'], $toArray);
        $this->assertEquals($options['cc'], $ccArray);
        $this->assertEquals($options['bcc'], $bccArray);
        $this->assertEquals($options['subject'], $mailService->getMessage()->getSubject());
        $this->assertEquals($options['body'], $mailService->getMessage()->getBody());
    }

    public function testSmtpAdapter()
    {
        $options = array(
            'mail_adapter'  => 'Zend\Mail\Transport\Smtp',
            'server'        => 'the.host',
            'smtp_user'     => 'alejandro',
            'smtp_password' => '1234',
            'ssl'           => 'ssl',
            'port'          => 465
        );
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);

        /* @var Smtp $transport */
        $transport = $mailService->getTransport();
        $this->assertInstanceOf($options['mail_adapter'], $transport);
        $connConfig = $transport->getOptions()->getConnectionConfig();
        $this->assertEquals($options['smtp_user'], $connConfig['username']);
        $this->assertEquals($options['smtp_password'], $connConfig['password']);
        $this->assertEquals($options['ssl'], $connConfig['ssl']);
        $this->assertEquals($options['server'], $transport->getOptions()->getHost());
        $this->assertEquals($options['port'], $transport->getOptions()->getPort());
    }

    public function testFileAdapter()
    {
        $options = array(
            'mail_adapter'  => 'file',
            'file_path'     => __DIR__,
            'file_callback' => function ($transport) {
                return 'TheFilename.eml';
            }
        );
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);

        /* @var File $transport */
        $transport = $mailService->getTransport();
        $this->assertInstanceOf('Zend\Mail\Transport\File', $transport);
        $this->assertEquals($options['file_path'], $transport->getOptions()->getPath());
        $this->assertEquals($options['file_callback'], $transport->getOptions()->getCallback());
    }

    public function testAdapterAsService()
    {
        $this->initServiceLocator(array(
            'mail_adapter_service' => 'Zend\Mail\Transport\TransportInterface'
        ));
        $transport = new Sendmail();
        $this->serviceLocator->set('Zend\Mail\Transport\TransportInterface', $transport);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertSame($transport, $mailService->getTransport());
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testNonExistentAdapterAsService()
    {
        $this->initServiceLocator(array(
            'mail_adapter_service' => 'Zend\Mail\Transport\TransportInterface'
        ));
        $this->mailServiceFactory->createService($this->serviceLocator);
    }

    public function testViewRendererService()
    {
        $this->initServiceLocator();
        // Create the service with default configuration
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $mailService->getRenderer());
        $this->assertInstanceOf('Zend\View\Resolver\TemplatePathStack', $mailService->getRenderer()->resolver());

        // Set a template_map and unset the template_path_stack
        $config = $this->serviceLocator->get('Config');
        unset($config['view_manager']['template_path_stack']);
        $config['view_manager']['template_map'] = array();
        $this->serviceLocator->set('Config', $config);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $mailService->getRenderer());
        $this->assertInstanceOf('Zend\View\Resolver\TemplateMapResolver', $mailService->getRenderer()->resolver());

        // Set both a template_map and a template_path_stack
        $this->initServiceLocator();
        $config = $this->serviceLocator->get('Config');
        $config['view_manager']['template_map'] = array();
        $this->serviceLocator->set('Config', $config);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $mailService->getRenderer());
        $this->assertInstanceOf('Zend\View\Resolver\AggregateResolver', $mailService->getRenderer()->resolver());

        // Set a viewrenderer service and see if it is used
        $renderer = new PhpRenderer();
        $this->serviceLocator->set('mailviewrenderer', $renderer);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertSame($renderer, $mailService->getRenderer());
    }

    private function initServiceLocator(array $mailOptions = array())
    {
        $this->serviceLocator = new ServiceManagerMock(array(
            'AcMailer\Options\MailOptions' => new MailOptions($mailOptions),
            'Config' => include __DIR__ . '/../../config/module.config.php'
        ));
    }

    public function testTemplateBody()
    {
        $options = array(
            'template' => array(
                'use_template'  => true,
                'path'          => 'ac-mailer/mail-templates/layout',
                'children'      => array(
                    'content'   => array(
                        'path'   => 'ac-mailer/mail-templates/mail',
                    )
                )
            ),
            'body' => 'This body is not going to be used'
        );
        $this->initServiceLocator($options);

        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../view');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);
        $this->serviceLocator->set('mailviewrenderer', $renderer);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);

        $this->assertNotEquals($options['body'], $mailService->getMessage()->getBody());
        $this->assertInstanceOf('Zend\Mime\Message', $mailService->getMessage()->getBody());
    }

    public function testFileAttachments()
    {
        $cwd = getcwd();
        chdir(dirname(__DIR__));
        $options = array(
            'attachments' => array(
                'files' => array(
                    'attachments/file1',
                    'attachments/file2',
                    'invalid_file_1',
                    'invalid_file_2',
                ),
                'dir' => array(
                    'iterate'   => true,
                    'path'      => 'attachments/dir',
                    'recursive' => true,
                ),
            ),
        );
        $this->initServiceLocator($options);
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);

        $this->assertCount(4, $mailService->getAttachments());
        chdir($cwd);
    }
}
