<?php
declare(strict_types=1);

namespace AcMailer\Exception;

use InvalidArgumentException as SplInvalidArgumentException;

use function get_class;
use function gettype;
use function implode;
use function is_object;
use function sprintf;

class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface
{
    public static function fromValidTypes(array $types, $value, string $fieldName = 'value'): self
    {
        return new self(sprintf(
            'Provided %s is not valid. Expected one of ["%s"], but "%s" was provided',
            $fieldName,
            implode('", "', $types),
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
