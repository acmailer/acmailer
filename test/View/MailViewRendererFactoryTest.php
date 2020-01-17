<?php

declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MailViewRendererFactory;
use AcMailer\View\MezzioMailViewRenderer;
use AcMailer\View\MvcMailViewRenderer;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class MailViewRendererFactoryTest extends TestCase
{
    private MailViewRendererFactory $factory;

    public function setUp(): void
    {
        $this->factory = new MailViewRendererFactory();
    }

    /**
     * @test
     */
    public function ifStandardServiceIsFoundItIsReturned(): void
    {
        $theRenderer = $this->prophesize(TemplateRendererInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(true);
        $getViewRenderer = $container->get(TemplateRendererInterface::class)->willReturn($theRenderer);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(MezzioMailViewRenderer::class, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function ifOldStandardServiceIsFoundItIsReturned(): void
    {
        $theRenderer = new PhpRenderer();

        $container = $this->prophesize(ContainerInterface::class);
        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(false);
        $hasOldViewRenderer = $container->has('mailviewrenderer')->willReturn(true);
        $getViewRenderer = $container->get('mailviewrenderer')->willReturn($theRenderer);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(MvcMailViewRenderer::class, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $hasOldViewRenderer->shouldHaveBeenCalledTimes(1);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function ifStandardServicesAreNotFoundOneIsCreatedOnTheFly(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(false);
        $hasOldViewRenderer = $container->has('mailviewrenderer')->willReturn(false);
        $getConfig = $container->get('config')->willReturn([
            'view_manager' => [
                'template_map' => [],
                'template_path_stack' => [],
            ],
            'view_helpers' => [],
        ]);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(MvcMailViewRenderer::class, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $hasOldViewRenderer->shouldHaveBeenCalledTimes(1);
        $getConfig->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     * @dataProvider provideViewManagerConfigs
     */
    public function oneResolverIsUsedWhenOnlyOneTemplateConfigExists(
        array $viewManagerConfig,
        string $expectedResolver
    ): void {
        $container = $this->prophesize(ContainerInterface::class);

        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(false);
        $hasOldViewRenderer = $container->has('mailviewrenderer')->willReturn(false);
        $getConfig = $container->get('config')->willReturn([
            'view_manager' => $viewManagerConfig,
        ]);

        $result = $this->factory->__invoke($container->reveal());
        $ref = new ReflectionObject($result);
        $wrappedRenderer = $ref->getProperty('renderer');
        $wrappedRenderer->setAccessible(true);
        /** @var PhpRenderer $wrappedRenderer */
        $wrappedRenderer = $wrappedRenderer->getValue($result);

        $this->assertInstanceOf($expectedResolver, $wrappedRenderer->resolver());
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $hasOldViewRenderer->shouldHaveBeenCalledTimes(1);
        $getConfig->shouldHaveBeenCalledTimes(2);
    }

    public function provideViewManagerConfigs(): iterable
    {
        yield [[], Resolver\TemplatePathStack::class];
        yield [['template_map' => []], Resolver\TemplateMapResolver::class];
        yield [['template_path_stack' => []], Resolver\TemplatePathStack::class];
    }
}
