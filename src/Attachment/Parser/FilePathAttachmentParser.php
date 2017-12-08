<?php
declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Exception\InvalidAttachmentException;
use Zend\Mime;
use Zend\Mime\Exception\InvalidArgumentException;

class FilePathAttachmentParser implements AttachmentParserInterface
{
    /**
     * @var \finfo
     */
    private $finfo;

    public function __construct(\finfo $finfo = null)
    {
        $this->finfo = $finfo ?: new \finfo(\FILEINFO_MIME_TYPE);
    }

    /**
     * @param string|resource|array|Mime\Part $attachment
     * @param string|null $attachmentName
     * @return Mime\Part
     * @throws InvalidArgumentException
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, string $attachmentName = null): Mime\Part
    {
        if (! \is_string($attachment) || ! \is_file($attachment)) {
            throw InvalidAttachmentException::fromExpectedType('file path');
        }

        // If the attachment name is not defined, use the attachment's \basename
        $name = $attachmentName ?? \basename($attachment);

        $part = new Mime\Part(\fopen($attachment, 'r+b'));
        $part->type = $this->finfo->file($attachment);
        $part->id = $name;
        $part->filename = $name;

        // Make sure encoding and disposition have a default value
        $part->encoding = Mime\Mime::ENCODING_BASE64;
        $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        return $part;
    }
}
