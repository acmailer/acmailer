<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Exception\InvalidAttachmentException;
use Laminas\Mime\Part;

interface AttachmentParserInterface
{
    /**
     * @param string|resource|array|Part $attachment
     * @param string|null $attachmentName
     * @return Part
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, ?string $attachmentName = null): Part;
}
