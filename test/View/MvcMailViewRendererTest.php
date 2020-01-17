<?php

declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MvcMailViewRenderer;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class MvcMailViewRendererTest extends TestCase
{
    private MvcMailViewRenderer $mvcRenderer;
    private ObjectProphecy $innerRenderer;

    public function setUp(): void
    {
        $this->innerRenderer = $this->prophesize(RendererInterface::class);
        $this->mvcRenderer = new MvcMailViewRenderer($this->innerRenderer->reveal());
    }

    /**
     * @test
     */
    public function renderDelegatesIntoLaminasRendererWhenNoLayoutIsProvided(): void
    {
        $innerRender = $this->innerRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->mvcRenderer->render('foo');

        $innerRender->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function renderDelegatesIntoLaminasRendererWhenLayoutIsProvided(): void
    {
        $innerRender = $this->innerRenderer->render(Argument::type(ViewModel::class))->willReturn('');

        $this->mvcRenderer->render('foo', ['layout' => 'bar']);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     */
    public function parametersArePassedBothToLayoutAndChildTemplate(): void
    {
        $innerRender = $this->innerRenderer->render(Argument::that(function (ViewModel $viewModel) {
            $variables = $viewModel->getVariables();
            Assert::assertArrayHasKey('foo', $variables);
            Assert::assertArrayHasKey('baz', $variables);
            return $viewModel;
        }))->willReturn('');

        $this->mvcRenderer->render('foo', ['layout' => 'bar', 'foo' => 'bar', 'baz' => 'foo']);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     * @dataProvider provideChildTemplateNames
     */
    public function childTemplateNameIsProperlySet(string $expectedName, array $params): void
    {
        $invocationCount = 0;
        $innerRender = $this->innerRenderer->render(Argument::that(function (ViewModel $viewModel) use (
            $expectedName,
            &$invocationCount
        ) {
            if ($invocationCount === 1) {
                Assert::assertArrayHasKey($expectedName, $viewModel->getVariables());
            }
            $invocationCount++;
            return $viewModel;
        }))->willReturn('');

        $this->mvcRenderer->render('foo', $params);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }

    public function provideChildTemplateNames(): iterable
    {
        yield ['content', ['layout' => 'bar']];
        yield ['foobar', ['child_template_name' => 'foobar', 'layout' => 'bar']];
    }
}
