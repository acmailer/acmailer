<?php
namespace AcMailerTest\Service;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Service\MailServiceInterface;
use AcMailer\View\DefaultLayout;
use AcMailerTest\Event\MailListenerMock;
use Zend\Mail\Message;
use AcMailerTest\Mail\Transport\MockTransport;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use AcMailer\Service\MailService;
use Zend\Mime;
use AcMailer\Result\MailResult;
use Zend\View\Resolver\TemplatePathStack;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Mail service test case
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceTest extends TestCase
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
        $this->transport = new MockTransport();
        $config = include __DIR__ . '/../../config/module.config.php';
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack($config['view_manager']['template_path_stack']));
        $this->mailService = new MailService(new Message(), $this->transport, $renderer);
    }
    
    public function testMimePartBodyCasting()
    {
        $this->mailService->setBody(new Mime\Part('Foo'));
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof Mime\Message);

        /** @var Mime\Message $body */
        $body = $this->mailService->getMessage()->getBody();
        $this->assertNull($body->getParts()[0]->charset);

        $this->mailService->setBody(new Mime\Part('Foo'), MailServiceInterface::DEFAULT_CHARSET);
        /** @var Mime\Message $body */
        $body = $this->mailService->getMessage()->getBody();
        $this->assertEquals(MailServiceInterface::DEFAULT_CHARSET, $body->getParts()[0]->charset);
    }
    
    public function testHtmlBodyCasting()
    {
        $this->mailService->setBody('<div>Html body</div>');
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof Mime\Message);
    }
    
    public function testStringBodyCasting()
    {
        $expected = 'String body';
        $this->mailService->setBody($expected);
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof Mime\Message);
    }
    
    public function testMimeMessageBodyRemainsUnchanged()
    {
        $part       = new Mime\Part('Foo');
        $message    = new Mime\Message();
        $message->addPart($part);
        $this->mailService->setBody($message);
        
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof Mime\Message);
        $this->assertEquals($message, $this->mailService->getMessage()->getBody());
    }

    public function testCharsetIsRespectedWhenSettingHtmlStringBody()
    {
        $expected = 'foo';
        $this->mailService->setBody('<h2>string</h2>', $expected);
        /** @var Mime\Message $body */
        $body = $this->mailService->getMessage()->getBody();
        $part = $body->getParts();
        $this->assertCount(1, $part);

        /** @var Mime\Part $part */
        $part = $part[0];
        $this->assertEquals($expected, $part->charset);
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
        $expected = 'This is the subject';
        
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

    /**
     * @expectedException \Exception
     */
    public function testWithUncatchedException()
    {
        $this->transport->setForceError(true, new \Exception());
        $this->mailService->send();
    }

    public function testZendMailExceptionsAreNotRethrown()
    {
        $this->transport->setForceError(true, new \Zend\Mail\Exception\InvalidArgumentException());
        $result = $this->mailService->send();
        $this->assertFalse($result->isValid());

        $this->transport->setForceError(true, new \Zend\Mail\Exception\BadMethodCallException());
        $result = $this->mailService->send();
        $this->assertFalse($result->isValid());

        $this->transport->setForceError(true, new \Zend\Mail\Protocol\Exception\InvalidArgumentException());
        $result = $this->mailService->send();
        $this->assertFalse($result->isValid());
    }

    public function testSetTransport()
    {
        $this->assertSame($this->transport, $this->mailService->getTransport());
        $anotherTransport = new MockTransport();
        $this->assertSame($this->mailService, $this->mailService->setTransport($anotherTransport));
        $this->assertSame($anotherTransport, $this->mailService->getTransport());
    }

    public function testSetRenderer()
    {
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $this->mailService->getRenderer());
        $anotherRenderer = new PhpRenderer();
        $this->assertSame($this->mailService, $this->mailService->setRenderer($anotherRenderer));
        $this->assertSame($anotherRenderer, $this->mailService->getRenderer());
    }

    public function testSuccesfulMailEvent()
    {
        $mailListener = new MailListenerMock();
        $this->mailService->attachMailListener($mailListener);
        $result = $this->mailService->send();

        $this->assertTrue($result->isValid());
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

        $this->assertFalse($result->isValid());
        $this->assertTrue($mailListener->isOnPreSendCalled());
        $this->assertFalse($mailListener->isOnPostSendCalled());
        $this->assertTrue($mailListener->isOnSendErrorCalled());
    }

    public function testDetachedMailListenerIsNotTriggered()
    {
        $mailListener = new MailListenerMock();
        $this->mailService->attachMailListener($mailListener);
        $this->mailService->detachMailListener($mailListener);
        $result = $this->mailService->send();

        $this->assertTrue($result->isValid());
        $this->assertFalse($mailListener->isOnPreSendCalled());
        $this->assertFalse($mailListener->isOnPostSendCalled());
        $this->assertFalse($mailListener->isOnSendErrorCalled());
    }

    public function testValidTemplateMakesBodyToBeMimeMessage()
    {
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../view');
        $this->mailService->getRenderer()->setResolver($resolver);
        $this->mailService->setTemplate('ac-mailer/mail-templates/mail.phtml');

        $this->assertInstanceOf('Zend\Mime\Message', $this->mailService->getMessage()->getBody());
    }

    /**
     * @expectedException \Zend\View\Exception\RuntimeException
     */
    public function testInvalidTemplateThrowsException()
    {
        $this->mailService->setTemplate('foo/bar');
    }

    public function testAttachmentsTotal()
    {
        $this->assertCount(0, $this->mailService->getAttachments());

        $this->mailService->setAttachments(['one', 'two', 'three']);
        $this->mailService->addAttachments(['four', 'five', 'six']);
        $this->mailService->addAttachment('seven');
        $this->mailService->addAttachment('eight', 'with-alias');
        $this->assertCount(8, $this->mailService->getAttachments());

        $this->mailService->setAttachments(['one', 'two']);
        $this->assertCount(2, $this->mailService->getAttachments());

        $this->mailService->addAttachments(['three', 'four']);
        $this->assertCount(4, $this->mailService->getAttachments());
    }

    public function testAttachmentsAreAddedAsMimeParts()
    {
        $cwd = getcwd();
        chdir(dirname(__DIR__));
        $this->mailService->setAttachments([
            'attachments/file1',
            'attachments/file2',
            'attachments/dir/file3',
            'invalid/attachment'
        ]);
        $this->mailService->setBody('Body as string');
        $result = $this->mailService->send();
        $this->assertTrue($result->isValid());

        /* @var Mime\Message $body */
        $body = $this->mailService->getMessage()->getBody();
        $this->assertInstanceOf('Zend\Mime\Message', $body);
        // The body and the three attached files make it a total of 4 parts
        $this->assertCount(4, $body->getParts());
        chdir($cwd);
    }

    public function testStringBypassedBodyIsWrappedIntoMimePartWithAttachments()
    {
        $cwd = getcwd();
        chdir(dirname(__DIR__));
        $this->mailService->setAttachments([
            'attachments/file1',
            'attachments/file2'
        ]);
        $this->mailService->getMessage()->setBody('Btpassed body as string');
        $result = $this->mailService->send();
        $this->assertTrue($result->isValid());

        /* @var Mime\Message $body */
        $body = $this->mailService->getMessage()->getBody();
        $this->assertInstanceOf('Zend\Mime\Message', $body);
        chdir($cwd);
    }

    public function testWithDefaultLayout()
    {
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../view');
        $this->mailService->getRenderer()->setResolver($resolver);

        $model = new ViewModel();
        $model->setTemplate('ac-mailer/mail-templates/layout.phtml');
        $this->mailService->setDefaultLayout(new DefaultLayout($model));
        $this->mailService->setTemplate('ac-mailer/mail-templates/mail.phtml');
        $this->assertInstanceOf('Zend\Mime\Message', $this->mailService->getMessage()->getBody());
    }
}
