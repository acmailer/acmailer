<?php

declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\EventDispatcher;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Event\PostSendEvent;
use AcMailer\Event\PreRenderEvent;
use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

class EventDispatcherTest extends TestCase
{
    use ProphecyTrait;

    private EventDispatcher $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    /** @test */
    public function onlyAttachedListenersAreInvokedWhenDispatching(): void
    {
        [$listenerA, $listenerB, $listenerC] = $this->attachMailListenerMocks();

        $e = new PreRenderEvent(new Email());
        $this->dispatcher->dispatch($e);

        $listenerA->onPreRender($e)->shouldHaveBeenCalledOnce();
        $listenerB->onPreRender($e)->shouldHaveBeenCalledOnce();
        $listenerC->onPreRender($e)->shouldHaveBeenCalledOnce();

        $this->dispatcher->detachMailListener($listenerB->reveal());

        $e = new PostSendEvent(new Email(), new MailResult(new Email()));
        $this->dispatcher->dispatch($e);

        $listenerA->onPostSend($e)->shouldHaveBeenCalledOnce();
        $listenerB->onPostSend(Argument::any())->shouldNotBeCalled();
        $listenerC->onPostSend($e)->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function noMethodsAreCalledOnListenersWhenDIspatchingUnknownEvent(): void
    {
        [$listenerA, $listenerB, $listenerC] = $this->attachMailListenerMocks();

        $this->dispatcher->dispatch(new stdClass());

        $methods = ['onPreRender', 'onPreSend', 'onPostSend', 'onSendError'];
        foreach ($methods as $method) {
            $listenerA->__call($method, [Argument::cetera()])->shouldNotBeCalled();
            $listenerB->__call($method, [Argument::cetera()])->shouldNotBeCalled();
            $listenerC->__call($method, [Argument::cetera()])->shouldNotBeCalled();
        }
    }

    private function attachMailListenerMocks(): array
    {
        $listenerA = $this->prophesize(MailListenerInterface::class);
        $listenerB = $this->prophesize(MailListenerInterface::class);
        $listenerC = $this->prophesize(MailListenerInterface::class);

        $this->dispatcher->attachMailListener($listenerA->reveal());
        $this->dispatcher->attachMailListener($listenerB->reveal(), 2);
        $this->dispatcher->attachMailListener($listenerC->reveal());

        return [$listenerA, $listenerB, $listenerC];
    }
}
