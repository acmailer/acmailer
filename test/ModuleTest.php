<?php
namespace AcMailerTest;

use AcMailer\Module;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class ModuleTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ModuleTest extends TestCase
{
    /**
     * @var Module
     */
    private $module;

    public function setUp()
    {
        $this->module = new Module();
    }

    public function testGetAutoloaderConfig()
    {
        $autoloaderConfig = $this->module->getAutoloaderConfig();

        $this->assertTrue(is_array($autoloaderConfig));
        $this->assertArrayHasKey('Zend\Loader\ClassMapAutoloader', $autoloaderConfig);
        $this->assertArrayHasKey('Zend\Loader\StandardAutoloader', $autoloaderConfig);
    }

    public function testGetConfig()
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $returnedConfig = $this->module->getConfig();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }
}
