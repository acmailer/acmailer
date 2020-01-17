<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Attachment\Helper\AttachmentHelperTrait;
use AcMailer\Exception\InvalidAttachmentException;
use Laminas\Mime;
use Laminas\Mime\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArrayUtils;

use function is_array;
use function method_exists;
use function str_replace;

class ArrayAttachmentParser implements AttachmentParserInterface
{
    use AttachmentHelperTrait;

    /**
     * @param string|resource|array|Mime\Part $attachment
     * @throws InvalidArgumentException
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, ?string $attachmentName = null): Mime\Part
    {
        if (! is_array($attachment)) {
            throw InvalidAttachmentException::fromExpectedType('array');
        }

        // Set default values for certain properties in the Mime\Part object
        $attachment = ArrayUtils::merge([
            'encoding' => Mime\Mime::ENCODING_BASE64,
            'disposition' => Mime\Mime::DISPOSITION_ATTACHMENT,
        ], $attachment);

        // Map a Mime\Part object with the array properties
        $part = new Mime\Part();
        foreach ($attachment as $property => $value) {
            $method = $this->buildSetter($property);
            if (method_exists($part, $method)) {
                $part->{$method}($value);
            }
        }

        return $this->applyNameToPart($part, $attachmentName);
    }

    private function buildSetter(string $property): string
    {
        return 'set' . str_replace('_', ' ', $property);
    }
}
