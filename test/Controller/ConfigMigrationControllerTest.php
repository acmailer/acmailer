<?php
namespace AcMailerTest\Controller;

use AcMailer\Controller\ConfigMigrationController;
use AcMailer\Service\ConfigMigrationService;
use AcMailerTest\Console\AdapterMock;
use Zend\Console\Request;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Stdlib\Parameters;

/**
 * Class ConfigMigrationControllerTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationControllerTest extends TestCase
{
    /**
     * @var ConfigMigrationController
     */
    private $controller;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var AdapterMock
     */
    private $console;

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testParseConfigWithInvalidFormatThrowsException()
    {
        $this->initController();
        $this->setRequestParams(['format' => 'invalid']);
        $this->controller->parseConfigAction();
    }

    public function testInvalidConfigKeyReturnsError()
    {
        $this->initController();
        $this->setRequestParams(['configKey' => 'invalid']);
        $this->controller->parseConfigAction();
        $this->assertEquals(
            'It wasn\'t possible to find the configuration key "invalid"',
            $this->console->getLines()[0]
        );
    }

    public function testDumpOutput()
    {
        $this->initController([
            'mail_options' => []
        ]);
        $this->setRequestParams(['format' => 'json']);
        $this->controller->parseConfigAction();
        $this->assertEquals([
            0 => 'This is your new configuration for the AcMailer module:',
            1 => '{"acmailer_options":{"default":{"message_options":{"body":[]},'
                . '"smtp_options":{"connection_config":[]},"file_options":[]}}}'
        ], $this->console->getLines());
    }

    public function testOutputFile()
    {
        $this->initController([
            'mail_options' => []
        ]);
        $expectedFile = __DIR__ . '/../attachments/mail_config.json';
        $this->setRequestParams(['format' => 'json', 'outputFile' => $expectedFile]);
        $this->controller->parseConfigAction();
        $this->assertTrue(file_exists($expectedFile));
        $this->assertEquals(
            '{"acmailer_options":{"default":{"message_options":{"body":[]},'
            . '"smtp_options":{"connection_config":[]},"file_options":[]}}}',
            file_get_contents($expectedFile)
        );
        unlink($expectedFile);
    }

    private function initController(array $globalConfig = [])
    {
        $this->controller = new ConfigMigrationController(new ConfigMigrationService(), $globalConfig);
        $this->console = new AdapterMock();
        $this->controller->setConsole($this->console);
        $this->request = new Request();

        // Set the request via reflection
        $refClass = new \ReflectionClass($this->controller);
        $property = $refClass->getProperty('request');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->request);
    }

    private function setRequestParams(array $params = [])
    {
        $this->request->setParams(new Parameters($params));
    }
}
