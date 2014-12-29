<?php
namespace AcMailerTest\Controller\Plugin;

use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Service\MailServiceMock;
use PHPUnit_Framework_TestCase as TestCase;

class SendMailPluginTest extends TestCase
{
    /**
     * @var SendMailPlugin
     */
    private $plugin;

    public function setUp()
    {
        $this->plugin = new SendMailPlugin(new MailServiceMock());
    }

    public function testInvokeWithNoArgumentsReturnsTheService()
    {
        $this->assertInstanceOf('AcMailer\Service\MailServiceInterface', $this->plugin->__invoke());
    }
}
