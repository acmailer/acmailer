<?php

declare(strict_types=1);

namespace AcMailer\Event;

use function get_class;
use function krsort;

class EventDispatcher implements EventDispatcherInterface
{
    private const EVENT_METHOD_MAP = [
        PreRenderEvent::class => 'onPreRender',
        PreSendEvent::class => 'onPreSend',
        PostSendEvent::class => 'onPostSend',
        SendErrorEvent::class => 'onSendError',
    ];

    private array $listenersQueue = [];

    public function dispatch(object $event): DispatchResult
    {
        $result = new DispatchResult();
        $methodToCall = self::EVENT_METHOD_MAP[get_class($event)] ?? null;

        if ($methodToCall === null) {
            return $result;
        }

        // Order listeners by priority
        krsort($this->listenersQueue);

        foreach ($this->listenersQueue as $priority => $listeners) {
            /** @var MailListenerInterface $listener */
            foreach ($listeners as $listener) {
                $result->push($listener->{$methodToCall}($event));
            }
        }

        return $result;
    }

    public function attachMailListener(MailListenerInterface $mailListener, int $priority = 1): void
    {
        $this->listenersQueue[$priority][] = $mailListener;
    }

    public function detachMailListener(MailListenerInterface $mailListener): void
    {
        foreach ($this->listenersQueue as $priority => $listeners) {
            foreach ($listeners as $index => $listener) {
                if ($listener === $mailListener) {
                    unset($this->listenersQueue[$priority][$index]);
                }
            }

            if (empty($this->listenersQueue[$priority])) {
                unset($this->listenersQueue[$priority]);
            }
        }
    }
}
