<?php
namespace AcMailerTest\Controller\Plugin;

use AcMailer\Controller\Plugin\Factory\SendMailPluginAbstractFactory;
use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailServiceMock;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

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
        $sm = $this->createServiceManager();
        $this->assertFalse($this->factory->canCreate($sm, 'foo'));
    }

    public function testCanCreateServiceWithBaseName()
    {
        $sm = $this->createServiceManager();
        $this->assertTrue($this->factory->canCreate($sm, 'sendMail'));
    }

    public function testCanCreateServiceWhenConcreteServiceIsNotDefined()
    {
        $sm = $this->createServiceManager([
            'acmailer_options' => [
                'concrete' => []
            ]
        ]);
        $this->assertTrue($this->factory->canCreate($sm, 'sendMailConcrete'));
        $this->assertFalse($this->factory->canCreate($sm, 'sendMailInvalid'));
    }

    public function testCreateServiceWithName()
    {
        $sm = $this->createServiceManager();
        $mailServiceName = sprintf(
            '%s.%s.%s',
            MailServiceAbstractFactory::ACMAILER_PART,
            MailServiceAbstractFactory::SPECIFIC_PART,
            'concrete'
        );
        $sm->setService($mailServiceName, new MailServiceMock());
        $this->assertInstanceOf(SendMailPlugin::class, $this->factory->__invoke($sm, 'sendMailConcrete'));
    }

    protected function createServiceManager($config = [])
    {
        $sm = new ServiceManager();
        $sm->setService('Config', $config);

        return $sm;
    }
}
