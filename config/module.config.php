<?php
declare(strict_types=1);

namespace AcMailer;

use Zend\Mime\Part;
use Zend\ServiceManager\Factory\InvokableFactory;

return [

    'acmailer_options' => [
        'emails' => [],

        'mail_services' => [
            'default' => [],
        ],

        'attachment_parsers' => [
            'factories' => [
                Attachment\Parser\ArrayAttachmentParser::class => InvokableFactory::class,
                Attachment\Parser\FilePathAttachmentParser::class => InvokableFactory::class,
                Attachment\Parser\MimePartAttachmentParser::class => InvokableFactory::class,
                Attachment\Parser\ResourceAttachmentParser::class => InvokableFactory::class,
            ],

            'aliases' => [
                'array' => Attachment\Parser\ArrayAttachmentParser::class,
                'string' => Attachment\Parser\FilePathAttachmentParser::class,
                Part::class => Attachment\Parser\MimePartAttachmentParser::class,
                'resource' => Attachment\Parser\ResourceAttachmentParser::class,
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            Attachment\AttachmentParserManager::class => Attachment\AttachmentParserManagerFactory::class,
            Model\EmailBuilder::class => Model\EmailBuilderFactory::class,
            'acmailer.mailservice.default' => Service\Factory\MailServiceAbstractFactory::class,
            View\MailViewRendererFactory::SERVICE_NAME => View\MailViewRendererFactory::class,
        ],

        'abstract_factories' => [
            Service\Factory\MailServiceAbstractFactory::class,
        ],

        'aliases' => [
            Service\MailServiceInterface::class => 'acmailer.mailservice.default',
            Service\MailService::class => 'acmailer.mailservice.default',
            'mailservice' => 'acmailer.mailservice.default',

            'mailviewrenderer' => 'ViewRenderer',
        ],
    ],

];
