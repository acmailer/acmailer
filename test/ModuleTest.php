<?php
declare(strict_types=1);

namespace AcMailerTest;

use AcMailer\Module;
use PHPUnit\Framework\TestCase;

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
}
