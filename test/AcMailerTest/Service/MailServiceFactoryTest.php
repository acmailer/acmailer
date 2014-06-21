<?php
namespace AcMailerTest\Service;

use AcMailer\Options\MailOptions;
use AcMailer\Service\Factory\MailServiceFactory;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use Zend\ServiceManager\ServiceLocatorInterface;

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
        $this->serviceLocator = new ServiceManagerMock(array(
            'AcMailer\Options\MailOptions' => new MailOptions(array())
        ));
        $this->mailServiceFactory = new MailServiceFactory();
    }

    public function testServiceIsCreated()
    {
        $mailService = $this->mailServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf('AcMailer\Service\MailService', $mailService);
    }
}
