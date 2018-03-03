<?php
declare(strict_types=1);

namespace AcMailer\Exception;

/**
 * Exception produced when an argument provided for an AcMailer method is not valid
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function fromValidTypes(array $types, $value, string $fieldName = 'value'): self
    {
        return new self(\sprintf(
            'Provided %s is not valid. Expected one of ["%s"], but "%s" was provided',
            $fieldName,
            \implode('", "', $types),
            \is_object($value) ? \get_class($value) : \gettype($value)
        ));
    }
}
