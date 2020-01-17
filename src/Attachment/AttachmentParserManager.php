<?php

declare(strict_types=1);

namespace AcMailer\Attachment;

use AcMailer\Attachment\Parser\AttachmentParserInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class AttachmentParserManager extends AbstractPluginManager implements AttachmentParserManagerInterface
{
    protected $instanceOf = AttachmentParserInterface::class;
}
