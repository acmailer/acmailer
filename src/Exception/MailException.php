<?php

declare(strict_types=1);

namespace AcMailer\Exception;

use RuntimeException;
use Throwable;

class MailException extends RuntimeException implements ExceptionInterface
{
    public static function fromThrowable(Throwable $prev): self
    {
        return new self('An error occurred while trying to send the email', $prev->getCode(), $prev);
    }
}
