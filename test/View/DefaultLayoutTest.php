<?php
namespace AcMailerTest\View;

use AcMailer\View\DefaultLayout;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\View\Model\ViewModel;

class DefaultLayoutTest extends TestCase
{
    public function testCreateWithDefaultValues()
    {
        $defaultLayout = new DefaultLayout();
        $this->assertFalse($defaultLayout->hasModel());
        $this->assertNull($defaultLayout->getModel());
        $this->assertEquals('content', $defaultLayout->getTemplateCaptureTo());
    }

    public function testCreateWithViewModelAndParams()
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'baz' => 'foo'
        ]);
        $defaultLayout = new DefaultLayout($model, [
            'more' => 'params',
            'to' => 'merge'
        ]);
        $this->assertTrue($defaultLayout->hasModel());
        $this->assertSame($model, $defaultLayout->getModel());
        $this->assertCount(4, $model->getVariables());
    }

    public function testCreateWithStringLayout()
    {
        $template = 'application/index/index';
        $defaultLayout = new DefaultLayout($template);
        $this->assertTrue($defaultLayout->hasModel());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $defaultLayout->getModel());
        $this->assertEquals($template, $defaultLayout->getModel()->getTemplate());
    }
}
