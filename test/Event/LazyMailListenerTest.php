<?php

declare(strict_types=1);

namespace AcMailerTest\Event;

use AcMailer\Event\LazyMailListener;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Event\PostSendEvent;
use AcMailer\Event\PreRenderEvent;
use AcMailer\Event\PreSendEvent;
use AcMailer\Event\SendErrorEvent;
use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class LazyMailListenerTest extends TestCase
{
    use ProphecyTrait;

    private LazyMailListener $listener;
    private ObjectProphecy $container;
    private ObjectProphecy $wrappedListener;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->listener = new LazyMailListener('foo', $this->container->reveal());
        $this->wrappedListener = $this->prophesize(MailListenerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideMethodName
     */
    public function wrappedEventIsCreatedOnceAndCallsAreProxied(string $methodName, object $event): void
    {
        $method = $this->wrappedListener->__call($methodName, [$event]);
        $getListener = $this->container->get('foo')->willReturn($this->wrappedListener->reveal());

        $this->listener->{$methodName}($event);
        $this->listener->{$methodName}($event);
        $this->listener->{$methodName}($event);

        $method->shouldHaveBeenCalledTimes(3);
        $getListener->shouldHaveBeenCalledOnce();
    }

    public function provideMethodName(): iterable
    {
        $email = new Email();
        $result = new MailResult($email);

        yield 'onPreRender' => ['onPreRender', new PreRenderEvent($email)];
        yield 'onPreSend' => ['onPreSend', new PreSendEvent($email)];
        yield 'onPostSend' => ['onPostSend', new PostSendEvent($email, $result)];
        yield 'onSendError' => ['onSendError', new SendErrorEvent($email, $result)];
    }
}
