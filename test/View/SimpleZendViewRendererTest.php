<?php
declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\SimpleZendViewRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\View\Renderer\RendererInterface;

class SimpleZendViewRendererTest extends TestCase
{
    /**
     * @var SimpleZendViewRenderer
     */
    private $simpleRenderer;
    /**
     * @var ObjectProphecy
     */
    private $zendRenderer;

    public function setUp()
    {
        $this->zendRenderer = $this->prophesize(RendererInterface::class);
        $this->simpleRenderer = new SimpleZendViewRenderer($this->zendRenderer->reveal());
    }

    /**
     * @test
     */
    public function renderDelegatesIntoZendRenderer()
    {
        $innerRender = $this->zendRenderer->render('foo', [])->willReturn('');

        $this->simpleRenderer->render('foo', []);

        $innerRender->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function getPathsReturnsEmpty()
    {
        $this->assertEquals([], $this->simpleRenderer->getPaths());
    }
}
