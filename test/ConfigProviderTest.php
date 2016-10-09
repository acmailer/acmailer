<?php
namespace AcMailerTest;

use AcMailer\ConfigProvider;
use PHPUnit_Framework_TestCase as TestCase;

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
        $expectedConfig = ['dependencies' => (include __DIR__ . '/../config/module.config.php')['service_manager']];
        $returnedConfig = $this->configProvider->__invoke();

        $this->assertEquals($expectedConfig, $returnedConfig);
    }
}
