<?php

declare(strict_types=1);

namespace AcMailer\Event;

interface MailListenerAwareInterface
{
    public function attachMailListener(MailListenerInterface $mailListener, int $priority = 1): void;

    public function detachMailListener(MailListenerInterface $mailListener): void;
}
