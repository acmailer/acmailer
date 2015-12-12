<?php
namespace AcMailerTest\Controller\Plugin;

use AcMailer\Controller\Plugin\Factory\SendMailPluginAbstractFactory;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailServiceMock;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;

/**
 * Class SendMailPluginFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPluginAbstractFactoryTest extends TestCase
{
    /**
     * @var SendMailPluginAbstractFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new SendMailPluginAbstractFactory();
    }

    public function testCanCreateServiceWithInvalidName()
    {
        $pm = $this->createControllerManager();
        $this->assertFalse($this->factory->canCreateServiceWithName($pm, '', 'foo'));
    }

    public function testCanCreateServiceWithBaseName()
    {
        $pm = $this->createControllerManager();
        $this->assertTrue($this->factory->canCreateServiceWithName($pm, '', 'sendMail'));
    }

    public function testCanCreateServiceWhenConcreteServiceIsNotDefined()
    {
        $pm = $this->createControllerManager([
            'acmailer_options' => [
                'concrete' => []
            ]
        ]);
        $this->assertTrue($this->factory->canCreateServiceWithName($pm, '', 'sendMailConcrete'));
        $this->assertFalse($this->factory->canCreateServiceWithName($pm, '', 'sendMailInvalid'));
    }

    public function testCreateServiceWithName()
    {
        $pm = $this->createControllerManager();
        $mailServiceName = sprintf(
            '%s.%s.%s',
            MailServiceAbstractFactory::ACMAILER_PART,
            MailServiceAbstractFactory::SPECIFIC_PART,
            'concrete'
        );
        $pm->getServiceLocator()->setService($mailServiceName, new MailServiceMock());
        $this->assertInstanceOf(
            'AcMailer\Controller\Plugin\SendMailPlugin',
            $this->factory->createServiceWithName($pm, '', 'sendMailConcrete')
        );
    }

    protected function createControllerManager($config = [])
    {
        $pm = new ControllerPluginManager();
        $sm = new ServiceManager();
        $sm->setService('Config', $config);
        $pm->setServiceLocator($sm);

        return $pm;
    }
}
