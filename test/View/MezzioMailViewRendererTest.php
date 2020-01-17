<?php

declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MezzioMailViewRenderer;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class MezzioMailViewRendererTest extends TestCase
{
    /** @var MezzioMailViewRenderer */
    private $expressiveRenderer;
    /** @var ObjectProphecy */
    private $innerRenderer;

    public function setUp(): void
    {
        $this->innerRenderer = $this->prophesize(TemplateRendererInterface::class);
        $this->expressiveRenderer = new MezzioMailViewRenderer($this->innerRenderer->reveal());
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
