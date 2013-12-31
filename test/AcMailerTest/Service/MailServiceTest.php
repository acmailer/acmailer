<?php
namespace AcMailerTest\Service;

use Zend\Mail\Message;
use AcMailerTest\Mail\Transport\MockTransport;
use Zend\View\Renderer\PhpRenderer;
use AcMailer\Service\MailService;
use Zend\Mime\Part;

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
    
    public function setUp() {
        $this->transport    = new MockTransport();
        $this->mailService  = new MailService(new Message(), $this->transport, new PhpRenderer());
    }
    
    public function testMimePartBodyCasting() {
        $this->mailService->setBody(new Part("Foo"));
        $this->assertTrue($this->mailService->getMessage()->getBody() instanceof \Zend\Mime\Message);
    }
    
    
    
}