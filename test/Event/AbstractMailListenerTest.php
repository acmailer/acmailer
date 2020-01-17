<?php

declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\AbstractMailListener;
use AcMailer\Event\MailEvent;
use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;

class AbstractMailListenerTest extends TestCase
{
    private AbstractMailListener $mailListener;

    public function setUp(): void
    {
        $this->mailListener = new class extends AbstractMailListener {
        };
    }

    /**
     * @test
     */
    public function listenersAreRegisteredWhenAttached(): void
    {
        $em = $this->prophesize(EventManagerInterface::class);
        $priority = 3;

        $attachPre = $em->attach(MailEvent::EVENT_MAIL_PRE_RENDER, [$this->mailListener, 'onPreRender'], $priority);
        $attachPreSend = $em->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this->mailListener, 'onPreSend'], $priority);
        $attachPost = $em->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this->mailListener, 'onPostSend'], $priority);
        $attachError = $em->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this->mailListener, 'onSendError'], $priority);

        $this->mailListener->attach($em->reveal(), $priority);

        $attachPre->shouldHaveBeenCalled();
        $attachPreSend->shouldHaveBeenCalled();
        $attachPost->shouldHaveBeenCalled();
        $attachError->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function defaultPriorityIsRespected(): void
    {
        $em = $this->prophesize(EventManagerInterface::class);

        $attachPre = $em->attach(MailEvent::EVENT_MAIL_PRE_RENDER, [$this->mailListener, 'onPreRender'], 1);
        $attachPreSend = $em->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this->mailListener, 'onPreSend'], 1);
        $attachPost = $em->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this->mailListener, 'onPostSend'], 1);
        $attachError = $em->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this->mailListener, 'onSendError'], 1);

        $this->mailListener->attach($em->reveal());

        $attachPre->shouldHaveBeenCalled();
        $attachPreSend->shouldHaveBeenCalled();
        $attachPost->shouldHaveBeenCalled();
        $attachError->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function allMethodsAreEmptyByDefault(): void
    {
        $event = $this->prophesize(MailEvent::class);

        $this->assertNull($this->mailListener->onPreRender($event->reveal()));
        $this->assertNull($this->mailListener->onPreSend($event->reveal()));
        $this->assertNull($this->mailListener->onPostSend($event->reveal()));
        $this->assertNull($this->mailListener->onSendError($event->reveal()));
    }
}
