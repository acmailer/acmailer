<?php

declare(strict_types=1);

namespace AcMailer\Exception;

use RuntimeException;

use function sprintf;

class MailCancelledException extends RuntimeException implements ExceptionInterface
{
    public static function fromEvent(string $eventName): self
    {
        return new self(sprintf('Email cancelled from "%s" event listener', $eventName));
    }
}
