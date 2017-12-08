<?php
declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use Zend\Mime\Part;

interface AttachmentParserInterface
{
    public function parse($attachment): Part;
}
