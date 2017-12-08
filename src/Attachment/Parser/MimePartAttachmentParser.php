<?php
declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Exception\InvalidAttachmentException;
use Zend\Mime;

class MimePartAttachmentParser implements AttachmentParserInterface
{
    /**
     * @param string|resource|array|Mime\Part $attachment
     * @param string|null $attachmentName
     * @return Mime\Part
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, string $attachmentName = null): Mime\Part
    {
        if (! $attachment instanceof Mime\Part) {
            throw InvalidAttachmentException::fromExpectedType(Mime\Part::class);
        }

        // If the attachment name was provided, use it for the id and filename
        if ($attachmentName !== null) {
            $attachment->id = $attachmentName;
            $attachment->filename = $attachmentName;
        }

        return $attachment;
    }
}
