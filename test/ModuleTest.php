<?php
namespace AcMailerTest;

use AcMailer\Module;
use AcMailerTest\Console\AdapterMock;
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

    public function testGetConfig()
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $returnedConfig = $this->module->getConfig();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }

    public function testGetConsoleUsage()
    {
        $this->assertEquals(
            [
                'acmailer parse-config [--configKey=] [--format=(php|xml|ini|json)] [--outputFile=]'
                => 'Parses the configuration of AcMailer module <=v4.5.1 to the structure used in v5.0.0'
            ],
            $this->module->getConsoleUsage(new AdapterMock())
        );
    }
}
