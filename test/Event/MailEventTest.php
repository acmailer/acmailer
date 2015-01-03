<?php
namespace AcMailerTest\Event;

use AcMailer\Event\MailEvent;
use AcMailer\Result\MailResult;
use AcMailer\Service\MailServiceMock;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MailEventTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailEventTest extends TestCase
{
    /**
     * @var MailEvent
     */
    private $mailEvent;

    public function testMailServiceInjection()
    {
        $mailService = new MailServiceMock();
        $this->mailEvent = new MailEvent($mailService);
        $this->assertSame($mailService, $this->mailEvent->getMailService());

        $mailService2 = new MailServiceMock();
        $this->mailEvent->setMailService($mailService2);
        $this->assertNotSame($mailService, $this->mailEvent->getMailService());
        $this->assertSame($mailService2, $this->mailEvent->getMailService());
    }

    public function testMailResultInjection()
    {
        $this->mailEvent = new MailEvent(new MailServiceMock());
        $result = new MailResult();
        $this->assertSame($this->mailEvent, $this->mailEvent->setResult($result));
        $this->assertSame($result, $this->mailEvent->getResult());
    }
}
