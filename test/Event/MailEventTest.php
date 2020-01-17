<?php

declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\MailEvent;
use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use PHPUnit\Framework\TestCase;

class MailEventTest extends TestCase
{
    private MailEvent $mailEvent;

    /**
     * @test
     */
    public function emailIsProperlyInjected(): void
    {
        $email = new Email();
        $this->mailEvent = new MailEvent($email);
        $this->assertSame($email, $this->mailEvent->getEmail());
    }

    /**
     * @test
     */
    public function resultIsProperlyInjection(): void
    {
        $email = new Email();
        $this->mailEvent = new MailEvent($email);
        $result = new MailResult($email);
        $this->assertSame($this->mailEvent, $this->mailEvent->setResult($result));
        $this->assertSame($result, $this->mailEvent->getResult());
    }
}
