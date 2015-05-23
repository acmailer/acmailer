<?php
namespace AcMailerTest\Controller;

use AcMailer\Controller\Factory\ConfigMigrationControllerFactory;
use AcMailer\Service\ConfigMigrationService;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\Controller\ControllerManager;

/**
 * Class ConfigMigrationControllerFactoryTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationControllerFactoryTest extends TestCase
{
    /** @var ConfigMigrationControllerFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new ConfigMigrationControllerFactory();
    }

    public function testCreateService()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ControllerManager $cm */
        $cm = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $cm->expects($this->any())
           ->method('getServiceLocator')
           ->willReturn(new ServiceManagerMock([
               'config' => ['mail_options' => []],
               'AcMailer\Service\ConfigMigrationService' => new ConfigMigrationService()
           ]));

        $this->assertInstanceOf(
            'AcMailer\Controller\ConfigMigrationController',
            $this->factory->createService($cm)
        );
    }
}
