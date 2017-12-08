<?php
declare(strict_types=1);

namespace AcMailer\Attachment;

use Interop\Container\ContainerInterface;
use Psr\Container;

class AttachmentParserManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AttachmentParserManager
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AttachmentParserManager
    {
        $config = $container->get('config');
        $attachmentParsers = $config['attachment_parsers'] ?? [];

        return new AttachmentParserManager($container, $attachmentParsers);
    }
}
