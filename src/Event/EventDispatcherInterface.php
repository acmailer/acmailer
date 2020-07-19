<?php

declare(strict_types=1);

namespace AcMailer\Event;

interface EventDispatcherInterface extends MailListenerHandlerInterface
{
    public function dispatch(object $event): DispatchResult;
}
