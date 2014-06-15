<?php
namespace AcMailerTest\Service;

use AcMailer\Exception\InvalidArgumentException;
use AcMailerTest\Event\MailListenerMock;
use Zend\Mail\Message;
use AcMailerTest\Mail\Transport\MockTransport;
use Zend\View\Renderer\PhpRenderer;
use AcMailer\Service\MailService;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use AcMailer\Result\MailResult;

/**
 * Mail service test case
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @var \AcMailerTest\Mail\Transport\MockTransport
     */
    private $transport;
    /**
     * @var \AcMailer\Service\MailService
     */
    private $mailService;
    
    public function setUp()
    {
        $this->transport    = new MockTransport();
        $this->mailService  = new MailService(new Message(), $this->transport, new PhpRenderer());
    }
    
    public function testMimePartBodyCasting()
    {
        $this->mailService->setBody(new MimePart("Foo"));
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof MimeMessage);
    }
    
    public function testHtmlBodyCasting()
    {
        $this->mailService->setBody("<div>Html body</div>");
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof MimeMessage);
    }
    
    public function testStringBodyRemainsUnchanged()
    {
        $expected = "String body";
        $this->mailService->setBody($expected);
        
        $this->assertTrue(is_string($this->mailService->getMessage()->getBody()));
        $this->assertEquals($expected, $this->mailService->getMessage()->getBody());
    }
    
    public function testMimeMessageBodyRemainsUnchanged()
    {
        $part       = new MimePart("Foo");
        $message    = new MimeMessage();
        $message->addPart($part);
        $this->mailService->setBody($message);
        
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof MimeMessage);
        $this->assertEquals($message, $this->mailService->getMessage()->getBody());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidBodyThrowsException()
    {
        $this->mailService->setBody(new \stdClass());
    }
    
    public function testSetSubject()
    {
        $expected = "This is the subject";
        
        $this->assertEquals($this->mailService, $this->mailService->setSubject($expected));
        $this->assertEquals($expected, $this->mailService->getMessage()->getSubject());
    }
    
    public function testSuccessfulSending()
    {
        $result = $this->mailService->send();
        
        $this->assertTrue($result->isValid());
        $this->assertEquals(MailResult::DEFAULT_MESSAGE, $result->getMessage());
    }
    
    public function testSendingWithError()
    {
        $this->transport->setForceError(true);
        $result = $this->mailService->send();
        
        $this->assertFalse($result->isValid());
        $this->assertEquals(MockTransport::ERROR_MESSAGE, $result->getMessage());
    }

    public function testSuccesfulMailEvent()
    {
        $mailListener = new MailListenerMock();
        $this->mailService->attachMailListener($mailListener);
        $result = $this->mailService->send();

        $this->assertTrue($mailListener->isOnPreSendCalled());
        $this->assertTrue($mailListener->isOnPostSendCalled());
        $this->assertFalse($mailListener->isOnSendErrorCalled());
    }

    public function testMailEventWithError()
    {
        $mailListener = new MailListenerMock();
        $this->transport->setForceError(true);
        $this->mailService->attachMailListener($mailListener);
        $result = $this->mailService->send();

        $this->assertTrue($mailListener->isOnPreSendCalled());
        $this->assertFalse($mailListener->isOnPostSendCalled());
        $this->assertTrue($mailListener->isOnSendErrorCalled());
    }

}