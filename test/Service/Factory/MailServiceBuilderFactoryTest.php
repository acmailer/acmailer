<?php
declare(strict_types=1);

namespace AcMailerTest\Service\Factory;

use AcMailer\Service\Factory\MailServiceBuilderFactory;
use AcMailer\Service\MailServiceInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionObject;

class MailServiceBuilderFactoryTest extends TestCase
{
    /** @var MailServiceBuilderFactory */
    private $factory;
    /** @var ObjectProphecy */
    private $container;

    public function setUp(): void
    {
        $this->factory = new MailServiceBuilderFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function properDependenciesArePassedToMailServiceBuilder(?array $config, array $expectedDependencies): void
    {
        $hasConfig = $this->container->has('config')->willReturn($config !== null);
        $getConfig = $this->container->get('config')->willReturn($config);

        $service = ($this->factory)($this->container->reveal(), '');
        $ref = new ReflectionObject($service);
        $servicesProp = $ref->getProperty('services');
        $servicesProp->setAccessible(true);

        $this->assertEquals($expectedDependencies, $servicesProp->getValue($service));
        $hasConfig->shouldHaveBeenCalledOnce();
        $getConfig->shouldHaveBeenCalledTimes($config === null ? 0 : 1);
    }

    public function provideConfigs(): iterable
    {
        $dependencies = ['services' => [
            'foo' => $this->prophesize(MailServiceInterface::class)->reveal(),
            'bar' => $this->prophesize(MailServiceInterface::class)->reveal(),
            'baz' => $this->prophesize(MailServiceInterface::class)->reveal(),
        ]];
        $serviceManager = ['services' => [
            'something' => $this->prophesize(MailServiceInterface::class)->reveal(),
            'something_else' => $this->prophesize(MailServiceInterface::class)->reveal(),
        ]];

        yield 'no config' => [null, []];
        yield 'empty config' => [[], []];
        yield 'dependencies in config' => [['dependencies' => $dependencies], $dependencies['services']];
        yield 'service_manager in config' => [['service_manager' => $serviceManager], $serviceManager['services']];
    }
}
