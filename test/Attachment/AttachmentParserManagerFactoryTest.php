<?php

declare(strict_types=1);

namespace AcMailerTest\Attachment;

use AcMailer\Attachment\AttachmentParserManagerFactory;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class AttachmentParserManagerFactoryTest extends TestCase
{
    private AttachmentParserManagerFactory $factory;

    public function setUp(): void
    {
        $this->factory = new AttachmentParserManagerFactory();
    }

    /**
     * @test
     */
    public function serviceIsProperlyCreated(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'attachment_parsers' => [
                'services' => [
                    'foo' => $this->prophesize(AttachmentParserInterface::class)->reveal(),
                ],
            ],

            'acmailer_options' => [
                'attachment_parsers' => [
                    'services' => [
                        'bar' => $this->prophesize(AttachmentParserInterface::class)->reveal(),
                    ],
                ],
            ],
        ]);

        $instance = $this->factory->__invoke($container->reveal());

        $this->assertTrue($instance->has('foo'));
        $this->assertTrue($instance->has('bar'));
        $this->assertFalse($instance->has('other'));
    }
}
