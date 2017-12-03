<?php
namespace AcMailerTest\Event;

use AcMailer\Event\MailEvent;
use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use PHPUnit\Framework\TestCase;

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

    /**
     * @test
     */
    public function emailIsProperlyInjected()
    {
        $email = new Email();
        $this->mailEvent = new MailEvent($email);
        $this->assertSame($email, $this->mailEvent->getEmail());
    }

    /**
     * @test
     */
    public function resultIsProperlyInjection()
    {
        $email = new Email();
        $this->mailEvent = new MailEvent($email);
        $result = new MailResult($email);
        $this->assertSame($this->mailEvent, $this->mailEvent->setResult($result));
        $this->assertSame($result, $this->mailEvent->getResult());
    }
}
