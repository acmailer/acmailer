<?php
declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Exception\InvalidAttachmentException;
use Zend\Mime\Exception\InvalidArgumentException;
use Zend\Mime;

class ResourceAttachmentParser implements AttachmentParserInterface
{
    /**
     * @param string|resource|array|Mime\Part $attachment
     * @param string|null $attachmentName
     * @return Mime\Part
     * @throws InvalidArgumentException
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, string $attachmentName = null): Mime\Part
    {
        if (! \is_resource($attachment)) {
            throw InvalidAttachmentException::fromExpectedType('resource');
        }

        $resourceData = \stream_get_meta_data($attachment);
        $name = $attachmentName ?? (isset($resourceData['uri']) ? \basename($resourceData['uri']) : null);

        $part = new Mime\Part($attachment);
        if ($name !== null) {
            $part->id = $name;
            $part->filename = $name;
        }

        // Make sure encoding and disposition have a default value
        $part->encoding = Mime\Mime::ENCODING_BASE64;
        $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        return $part;
    }
}
