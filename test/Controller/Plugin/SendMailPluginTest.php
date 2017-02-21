<?php
namespace AcMailerTest\Controller\Plugin;

use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Result\ResultInterface;
use AcMailer\Service\MailServiceInterface;
use AcMailer\Service\MailServiceMock;
use PHPUnit\Framework\TestCase;
use Zend\View\Model\ViewModel;

/**
 * Class SendMailPluginTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPluginTest extends TestCase
{
    /**
     * @var SendMailPlugin
     */
    private $plugin;
    /**
     * @var MailServiceInterface
     */
    private $service;

    public function setUp()
    {
        $this->service = new MailServiceMock();
        $this->plugin = new SendMailPlugin($this->service);
    }

    public function testInvokeWithNoArgumentsReturnsTheService()
    {
        $this->assertInstanceOf(MailServiceInterface::class, $this->plugin->__invoke());
    }

    public function testFirstArgumentArrayIsTreatedAsConfig()
    {
        $config = [
            'body' => 'foobar',
            'subject' => 'barfoo'
        ];

        $result = $this->plugin->__invoke($config);
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertEquals($config['body'], $this->service->getMessage()->getBody());
        $this->assertEquals($config['subject'], $this->service->getMessage()->getSubject());
    }

    public function testArgumentsAreProperlyMapped()
    {
        $result = $this->plugin->__invoke(
            'theBody',
            'theSubject',
            ['foobar@me.com'],
            ['from@me.com' => 'From Me'],
            ['cc@me.com'],
            ['bcc@me.com'],
            ['attachments/attachment1.zip', 'attachments/attachment2.zip'],
            ['reply@me.com' => 'Reply To Me'],
            'utf-8'
        );

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertEquals('theBody', $this->service->getMessage()->getBody());
        $this->assertEquals('theSubject', $this->service->getMessage()->getSubject());
        $this->assertEquals('foobar@me.com', $this->service->getMessage()->getTo()->current()->getEmail());
        $this->assertEquals('from@me.com', $this->service->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals('From Me', $this->service->getMessage()->getFrom()->current()->getName());
        $this->assertEquals('cc@me.com', $this->service->getMessage()->getCc()->current()->getEmail());
        $this->assertEquals('bcc@me.com', $this->service->getMessage()->getBcc()->current()->getEmail());
        $this->assertEquals('utf-8', $this->service->getMessage()->getEncoding());
        $this->assertEquals('reply@me.com', $this->service->getMessage()->getReplyTo()->current()->getEmail());
        $this->assertEquals('Reply To Me', $this->service->getMessage()->getReplyTo()->current()->getName());
    }

    public function testFromIsValidAsString()
    {
        $result = $this->plugin->__invoke('theBody', 'theSubject', ['foobar@me.com'], 'from@me.com');

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertEquals('from@me.com', $this->service->getMessage()->getFrom()->current()->getEmail());
    }
    
    public function testReplyToIsValidAsString()
    {
        $result = $this->plugin->__invoke(
            'theBody',
            'theSubject',
            ['foobar@me.com'],
            null,
            null,
            null,
            null,
            'replyTo@me.com'
        );

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertEquals('replyTo@me.com', $this->service->getMessage()->getReplyTo()->current()->getEmail());
    }

    public function testBodyIsValidAsViewModel()
    {
        $result = $this->plugin->__invoke(new ViewModel());

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertEquals('ViewModel body', $this->service->getMessage()->getBody());
    }

    public function testMailServiceAwareness()
    {
        $this->assertSame($this->service, $this->plugin->getMailService());
        $anotherService = new MailServiceMock();
        $this->assertSame($this->plugin, $this->plugin->setMailService($anotherService));
        $this->assertSame($anotherService, $this->plugin->getMailService());
    }
}
