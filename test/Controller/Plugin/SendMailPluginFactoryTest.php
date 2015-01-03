<?php
namespace AcMailerTest\Controller\Plugin;

use AcMailer\Controller\Plugin\Factory\SendMailPluginFactory;
use AcMailer\Service\MailServiceMock;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;

/**
 * Class SendMailPluginFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPluginFactoryTest extends TestCase
{
    /**
     * @var SendMailPluginFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new SendMailPluginFactory();
    }

    public function testCreateService()
    {
        $pm = new ControllerPluginManager();
        $sm = new ServiceManager();
        $sm->setService('AcMailer\Service\MailService', new MailServiceMock());
        $pm->setServiceLocator($sm);

        $this->assertInstanceOf('AcMailer\Controller\Plugin\SendMailPlugin', $this->factory->createService($pm));
    }
}
