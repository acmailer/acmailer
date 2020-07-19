<?php

declare(strict_types=1);

namespace AcMailer\Event;

class EventDispatcher implements EventDispatcherInterface
{
    private const EVENT_METHOD_MAP = [
        PreRenderEvent::class => 'onPreRender',
        PreSendEvent::class => 'onPreSend',
        PostSendEvent::class => 'onPostSend',
        SendErrorEvent::class => 'onSendError',
    ];

    public function dispatch(object $event): DispatchResult
    {
        return new DispatchResult();
    }

    public function attachMailListener(MailListenerInterface $mailListener, int $priority = 1): void
    {
        // TODO: Implement attachMailListener() method.
    }

    public function detachMailListener(MailListenerInterface $mailListener): void
    {
        // TODO: Implement detachMailListener() method.
    }
}
