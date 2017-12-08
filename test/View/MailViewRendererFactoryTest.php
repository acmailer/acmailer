<?php
declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MailViewRendererFactory;
use AcMailer\View\SimpleZendViewRenderer;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\View\Renderer\PhpRenderer;

class MailViewRendererFactoryTest extends TestCase
{
    /**
     * @var MailViewRendererFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new MailViewRendererFactory();
    }

    /**
     * @test
     */
    public function ifStandardServiceIsFoundItIsReturned()
    {
        $theRenderer = $this->prophesize(TemplateRendererInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(true);
        $getViewRenderer = $container->get(TemplateRendererInterface::class)->willReturn($theRenderer);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertSame($theRenderer, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function ifOldStandardServiceIsFoundItIsReturned()
    {
        $theRenderer = new PhpRenderer();

        $container = $this->prophesize(ContainerInterface::class);
        $hasViewRenderer = $container->has(TemplateRendererInterface::class)->willReturn(false);
        $hasOldViewRenderer = $container->has('mailviewrenderer')->willReturn(true);
        $getViewRenderer = $container->get('mailviewrenderer')->willReturn($theRenderer);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(SimpleZendViewRenderer::class, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $hasOldViewRenderer->shouldHaveBeenCalledTimes(1);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function ifStandardServicesAreNotFoundOneIsCreatedOnTheFly()
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

        $this->assertInstanceOf(SimpleZendViewRenderer::class, $result);
        $hasViewRenderer->shouldHaveBeenCalledTimes(1);
        $hasOldViewRenderer->shouldHaveBeenCalledTimes(1);
        $getConfig->shouldHaveBeenCalledTimes(2);
    }
}
