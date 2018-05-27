<?php
declare(strict_types=1);

namespace AcMailerTest\View;

use AcMailer\View\MvcMailViewRenderer;
use PHPUnit\Framework\Assert;
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

    /**
     * @test
     */
    public function parametersArePassedBothToLayoutAndChildTemplate()
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
     * @param string $expectedName
     * @param array $params
     */
    public function childTemplateNameIsProperlySet(string $expectedName, array $params)
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

    public function provideChildTemplateNames(): array
    {
        return [
            ['content', ['layout' => 'bar']],
            ['foobar', ['child_template_name' => 'foobar', 'layout' => 'bar']],
        ];
    }
}
