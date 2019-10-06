<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Helper;

use Zend\Mime\Part;

trait AttachmentHelperTrait
{
    private function applyNameToPart(Part $part, ?string $name = null): Part
    {
        if ($name !== null) {
            $part->id = $name;
            $part->filename = $name;
        }

        return $part;
    }
}
