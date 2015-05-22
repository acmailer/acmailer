<?php
namespace AcMailerTest\Options;

use AcMailer\Options\Factory\MailOptionsAbstractFactory;
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
