<?php
declare(strict_types=1);

namespace AcMailerTest\Model;

use AcMailer\Exception\ServiceNotCreatedException;
use AcMailer\Model\EmailBuilder;
use AcMailer\Model\EmailBuilderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EmailBuilderFactoryTest extends TestCase
{
    /** @var EmailBuilderFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new EmailBuilderFactory();
    }

    /**
     * @test
     */
    public function serviceIsCreated()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['acmailer_options' => ['emails' => []]]);

        $instance = $this->factory->__invoke($container->reveal());
        $this->assertInstanceOf(EmailBuilder::class, $instance);
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfConfigIsNotFound()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a config array in the container');

        $this->factory->__invoke($container->reveal());
    }
}
