<?php
declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MvcMailViewRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

class MvcMailViewRendererTest extends TestCase
{
    /**
     * @var MvcMailViewRenderer
     */
    private $mvcRenderer;
    /**
     * @var ObjectProphecy
     */
    private $innerRenderer;

    public function setUp()
    {
        $this->innerRenderer = $this->prophesize(RendererInterface::class);
        $this->mvcRenderer = new MvcMailViewRenderer($this->innerRenderer->reveal());
    }

    /**
     * @test
     */
    public function renderDelegatesIntoZendRendererWhenNoLayoutIsProvided()
    {
        $innerRender = $this->innerRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->mvcRenderer->render('foo');

        $innerRender->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function renderDelegatesIntoZendRendererWhenLayoutIsProvided()
    {
        $innerRender = $this->innerRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->mvcRenderer->render('foo', ['layout' => 'bar']);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }
}
