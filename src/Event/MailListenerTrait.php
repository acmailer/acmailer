<?php

declare(strict_types=1);

namespace AcMailer\Event;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateTrait;

trait MailListenerTrait
{
    use ListenerAggregateTrait;

    /**
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1): void // phpcs:ignore
    {
        foreach (MailListenerInterface::EVENT_METHOD_MAP as $event => $method) {
            $this->listeners[] = $events->attach($event, [$this, $method], $priority);
        }
    }
}
