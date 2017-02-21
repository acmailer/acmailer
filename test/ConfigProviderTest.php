<?php
namespace AcMailerTest;

use AcMailer\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->configProvider = new ConfigProvider();
    }

    public function testInvoke()
    {
        $moduleConfig = include __DIR__ . '/../config/module.config.php';
        $expectedConfig = ['dependencies' => $moduleConfig['service_manager']];
        $returnedConfig = $this->configProvider->__invoke();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }
}
