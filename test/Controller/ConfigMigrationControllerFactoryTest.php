<?php
namespace AcMailerTest\Controller;

use AcMailer\Controller\ConfigMigrationController;
use AcMailer\Controller\Factory\ConfigMigrationControllerFactory;
use AcMailer\Service\ConfigMigrationService;
use AcMailerTest\ServiceManager\ServiceManagerMock;
use PHPUnit\Framework\TestCase;

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
        $this->assertInstanceOf(
            ConfigMigrationController::class,
            $this->factory->__invoke(new ServiceManagerMock([
                'config' => ['mail_options' => []],
                ConfigMigrationService::class => new ConfigMigrationService()
            ]), '')
        );
    }
}
