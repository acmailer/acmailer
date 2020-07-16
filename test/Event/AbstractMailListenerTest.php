<?php

declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\AbstractMailListener;
use AcMailer\Event\PostSendEvent;
use AcMailer\Event\PreRenderEvent;
use AcMailer\Event\PreSendEvent;
use AcMailer\Event\SendErrorEvent;
use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AbstractMailListenerTest extends TestCase
{
    use ProphecyTrait;

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

        $attachPre = $em->attach(PreRenderEvent::class, [$this->mailListener, 'onPreRender'], $priority);
        $attachPreSend = $em->attach(PreSendEvent::class, [$this->mailListener, 'onPreSend'], $priority);
        $attachPost = $em->attach(PostSendEvent::class, [$this->mailListener, 'onPostSend'], $priority);
        $attachError = $em->attach(SendErrorEvent::class, [$this->mailListener, 'onSendError'], $priority);

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

        $attachPre = $em->attach(PreRenderEvent::class, [$this->mailListener, 'onPreRender'], 1);
        $attachPreSend = $em->attach(PreSendEvent::class, [$this->mailListener, 'onPreSend'], 1);
        $attachPost = $em->attach(PostSendEvent::class, [$this->mailListener, 'onPostSend'], 1);
        $attachError = $em->attach(SendErrorEvent::class, [$this->mailListener, 'onSendError'], 1);

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
        $email = new Email();
        $result = new MailResult($email);

        $this->assertNull($this->mailListener->onPreRender(new PreRenderEvent($email)));
        $this->assertNull($this->mailListener->onPreSend(new PreSendEvent($email)));
        $this->assertNull($this->mailListener->onPostSend(new PostSendEvent($email, $result)));
        $this->assertNull($this->mailListener->onSendError(new SendErrorEvent($email, $result)));
    }
}
