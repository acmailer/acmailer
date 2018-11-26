<?php
declare(strict_types=1);

namespace AcMailerTest\Attachment;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Attachment\AttachmentParserManagerFactory;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class AttachmentParserManagerFactoryTest extends TestCase
{
    /** @var AttachmentParserManagerFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new AttachmentParserManagerFactory();
    }

    /**
     * @test
     */
    public function serviceIsProperlyCreated()
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

        $this->assertInstanceOf(AttachmentParserManager::class, $instance);
        $this->assertTrue($instance->has('foo'));
        $this->assertTrue($instance->has('bar'));
        $this->assertFalse($instance->has('other'));
    }
}
