<?php
declare(strict_types=1);

namespace AcMailer\Attachment;

use Interop\Container\ContainerInterface;
use Psr\Container;
use Zend\Stdlib\ArrayUtils;

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
        $oldAttachmentParsers = $config['attachment_parsers'] ?? [];
        $attachmentParsers = $config['acmailer_options']['attachment_parsers'] ?? [];

        return new AttachmentParserManager($container, ArrayUtils::merge($oldAttachmentParsers, $attachmentParsers));
    }
}
