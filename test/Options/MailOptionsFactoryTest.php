<?php
namespace AcMailerTest\Options;

use AcMailer\Options\Factory\MailOptionsFactory;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use AcMailer\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MailOptionsFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsFactoryTest extends TestCase
{
    /**
     * @var MailOptionsFactory
     */
    private $mailOptionsFactory;
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    public function setUp()
    {
        $this->mailOptionsFactory = new MailOptionsFactory();
    }

    public function testEmptyConfigCreatesDefaultMailOptions()
    {
        $services = [
            'Config' => []
        ];
        $this->serviceLocator = new ServiceManagerMock($services);

        $mailOptions = $this->mailOptionsFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
    }

    public function testSomeCustomOptions()
    {
        $services = [
            'Config' => [
                'mail_options' => [
                    'to'        => 'foo@bar.com',
                    'smtp_user' => 'myuser'
                ]
            ]
        ];
        $this->serviceLocator = new ServiceManagerMock($services);

        $mailOptions = $this->mailOptionsFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals([$services['Config']['mail_options']['to']], $mailOptions->getTo());
        $this->assertEquals($services['Config']['mail_options']['smtp_user'], $mailOptions->getSmtpUser());
        $this->assertEquals([], $mailOptions->getCc());
        $this->assertEquals([], $mailOptions->getBcc());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIsThrownOnInvalidAdapter()
    {
        $services = [
            'Config' => [
                'mail_options' => [
                    'mail_adapter' => 'invalid',
                ]
            ]
        ];
        $this->serviceLocator = new ServiceManagerMock($services);
        $this->mailOptionsFactory->createService($this->serviceLocator);
    }
}
