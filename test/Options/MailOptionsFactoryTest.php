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
                'acmailer_options' => [
                    'message_options' => [
                        'to'    => 'foo@bar.com',
                        'from'  => 'Me',
                    ]
                ]
            ]
        ];
        $this->serviceLocator = new ServiceManagerMock($services);

        $mailOptions = $this->mailOptionsFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('AcMailer\Options\MailOptions', $mailOptions);
        $this->assertEquals(
            [$services['Config']['acmailer_options']['message_options']['to']],
            $mailOptions->getMessageOptions()->getTo()
        );
        $this->assertEquals(
            $services['Config']['acmailer_options']['message_options']['from'],
            $mailOptions->getMessageOptions()->getFrom()
        );
        $this->assertEquals([], $mailOptions->getMessageOptions()->getCc());
        $this->assertEquals([], $mailOptions->getMessageOptions()->getBcc());
    }
}
