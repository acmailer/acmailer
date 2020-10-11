<?php

declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MvcMailViewRenderer;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MvcMailViewRendererTest extends TestCase
{
    use ProphecyTrait;

    private MvcMailViewRenderer $mvcRenderer;
    private ObjectProphecy $innerRenderer;

    public function setUp(): void
    {
        $this->innerRenderer = $this->prophesize(RendererInterface::class);
        $this->mvcRenderer = new MvcMailViewRenderer($this->innerRenderer->reveal());
    }

    /** @test */
    public function renderDelegatesIntoLaminasRendererWhenNoLayoutIsProvided(): void
    {
        $innerRender = $this->innerRenderer->render(Argument::that(function (ViewModel $viewModel) {
            Assert::assertEquals('foo', $viewModel->getTemplate());
            return $viewModel;
        }))->willReturn('');

        $this->mvcRenderer->render('foo');

        $innerRender->shouldHaveBeenCalledTimes(1);
    }

    /** @test */
    public function renderDelegatesIntoLaminasRendererWhenLayoutIsProvided(): void
    {
        $innerRender = $this->innerRenderer->render(Argument::that(function (ViewModel $viewModel) {
            Assert::assertArrayNotHasKey('layout', $viewModel->getVariables());
            return $viewModel;
        }))->willReturn('');

        $this->mvcRenderer->render('foo', ['layout' => 'bar']);

        $innerRender->shouldHaveBeenCalledTimes(2);
    }

    /** @test */
    public function parametersArePassedBothToLayoutAndChildTemplate(): void
    {
        $callNum = 0;
        $innerRender = $this->innerRenderer->render(Argument::that(function (ViewModel $viewModel) {
            $variables = $viewModel->getVariables();
            Assert::assertArrayHasKey('foo', $variables);
            Assert::assertArrayHasKey('baz', $variables);

            return $viewModel;
        }))->will(function (array $args) use (&$callNum) {
            /** @var ViewModel $viewModel */
            [$viewModel] = $args;
            Assert::assertEquals($callNum === 0 ? 'foo' : 'bar', $viewModel->getTemplate());
            $callNum++;

            return '';
        });

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
