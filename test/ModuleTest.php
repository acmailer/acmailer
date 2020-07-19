<?php

declare(strict_types=1);

namespace AcMailerTest;

use AcMailer\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    private Module $module;

    public function setUp(): void
    {
        $this->module = new Module();
    }

    /** @test */
    public function getConfigReturnsContentsFromModuleConfigFile(): void
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $returnedConfig = $this->module->getConfig();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }

    /** @test */
    public function invokeReturnsContentsFromModuleConfigFile(): void
    {
        $expectedConfig = include __DIR__ . '/../config/module.config.php';
        $expectedConfig['dependencies'] = $expectedConfig['service_manager'];
        unset($expectedConfig['service_manager']);
        $returnedConfig = $this->module->__invoke();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }
}
