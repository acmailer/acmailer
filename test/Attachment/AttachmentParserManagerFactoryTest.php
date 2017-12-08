<?php
declare(strict_types=1);

namespace AcMailerTest\Attachment;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Attachment\AttachmentParserManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class AttachmentParserManagerFactoryTest extends TestCase
{
    /**
     * @var AttachmentParserManagerFactory
     */
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
        $container->get('config')->willReturn([]);

        $instance = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(AttachmentParserManager::class, $instance);
    }
}
