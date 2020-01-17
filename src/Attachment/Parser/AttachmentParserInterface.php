<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Exception\InvalidAttachmentException;
use Laminas\Mime\Part;

interface AttachmentParserInterface
{
    /**
     * @param string|resource|array|Part $attachment
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, ?string $attachmentName = null): Part;
}
