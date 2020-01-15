<?php

declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\ExpressiveMailViewRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Mezzio\Template\TemplateRendererInterface;

class ExpressiveMailViewRendererTest extends TestCase
{
    /** @var ExpressiveMailViewRenderer */
    private $expressiveRenderer;
    /** @var ObjectProphecy */
    private $innerRenderer;

    public function setUp(): void
    {
        $this->innerRenderer = $this->prophesize(TemplateRendererInterface::class);
        $this->expressiveRenderer = new ExpressiveMailViewRenderer($this->innerRenderer->reveal());
    }

    /**
     * @test
     */
    public function renderDelegatesIntoInnerRenderer(): void
    {
        $params = ['foo' => 'bar'];
        $innerRender = $this->innerRenderer->render('foo', $params)->willReturn('');

        $this->expressiveRenderer->render('foo', $params);

        $innerRender->shouldHaveBeenCalledTimes(1);
    }
}
