<?php
declare(strict_types=1);

namespace AcMailer\Exception;

use RuntimeException;

use function sprintf;

class InvalidAttachmentException extends RuntimeException implements ExceptionInterface
{
    public static function fromExpectedType(string $type): self
    {
        return new self(sprintf('Provided attachment is not valid. Expected "%s"', $type));
    }
}
