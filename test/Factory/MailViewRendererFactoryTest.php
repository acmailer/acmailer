<?php
declare(strict_types=1);

namespace AcMailerTest\Factory;

use AcMailer\View\MailViewRendererFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
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
        $theRenderer = new PhpRenderer();

        $container = $this->prophesize(ContainerInterface::class);
        $getViewRenderer = $container->get('viewrenderer')->willReturn($theRenderer);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertSame($theRenderer, $result);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function ifStandardServiceIsNotFoundOneIsCreatedOnTheFly()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $getViewRenderer = $container->get('viewrenderer')->willThrow(
            new class extends \Exception implements NotFoundExceptionInterface {
            }
        );
        $getConfig = $container->get('config')->willReturn([
            'view_manager' => [
                'template_map' => [],
                'template_path_stack' => [],
            ],
            'view_helpers' => [],
        ]);

        $result = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(PhpRenderer::class, $result);
        $getViewRenderer->shouldHaveBeenCalledTimes(1);
        $getConfig->shouldHaveBeenCalledTimes(2);
    }
}
