<?php
declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\AbstractMailListener;
use AcMailer\Event\MailEvent;
use PHPUnit\Framework\TestCase;
use Zend\EventManager\EventManagerInterface;

class AbstractMailListenerTest extends TestCase
{
    /**
     * @var AbstractMailListener
     */
    private $mailListener;

    public function setUp()
    {
        $this->mailListener = new class extends AbstractMailListener {
        };
    }

    /**
     * @test
     */
    public function listenersAreRegisteredWhenAttached()
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
    public function allMethodsAreEmptyByDefault()
    {
        $event = $this->prophesize(MailEvent::class);

        $this->assertNull($this->mailListener->onPreRender($event->reveal()));
        $this->assertNull($this->mailListener->onPreSend($event->reveal()));
        $this->assertNull($this->mailListener->onPostSend($event->reveal()));
        $this->assertNull($this->mailListener->onSendError($event->reveal()));
    }
}
