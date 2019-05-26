<?php
declare(strict_types=1);

namespace AcMailer\Exception;

use RuntimeException;

use function sprintf;

class EmailNotFoundException extends RuntimeException implements ExceptionInterface
{
    public static function fromName(string $name): self
    {
        return new self(sprintf('An email with name "%s" could not be found in registered emails list', $name));
    }
}
