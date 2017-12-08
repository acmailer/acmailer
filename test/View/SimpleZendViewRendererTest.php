<?php
declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\SimpleZendViewRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\View\Model\ViewModel;
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
    public function renderDelegatesIntoZendRendererWhenNoLayoutIsProvided()
    {
        $innerRender = $this->zendRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->simpleRenderer->render('foo');

        $innerRender->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function renderDelegatesIntoZendRendererWhenLayoutIsProvided()
    {
        $innerRender = $this->zendRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->simpleRenderer->render('foo', ['layout' => 'bar']);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     */
    public function getPathsReturnsEmpty()
    {
        $this->assertEquals([], $this->simpleRenderer->getPaths());
    }
}
