<?php
namespace AcMailerTest\Options;

use AcMailer\Options\TemplateOptions;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class TemplateOptionsTest
 * @author
 * @link
 */
class TemplateOptionsTest extends TestCase
{
    /**
     * @var TemplateOptions
     */
    private $templateOptions;

    public function setUp()
    {
        $this->templateOptions = new TemplateOptions();
    }

    public function testDefaultTemplateOptionsValues()
    {
        $this->assertEquals('ac-mailer/mail-templates/mail', $this->templateOptions->getPath());
        $this->assertEquals([], $this->templateOptions->getParams());
        $this->assertCount(0, $this->templateOptions->getParams());
        $this->assertEquals([], $this->templateOptions->getChildren());
        $this->assertCount(0, $this->templateOptions->getChildren());
        $this->assertEquals([], $this->templateOptions->getDefaultLayout());
    }

    public function testChildrenCastToTemplateOptions()
    {
        $children = [
            'content' => [
                'path'   => 'ac-mailer/content',
                'params' => [],
            ],
            'foo' => [
                'path'   => 'ac-mailer/foo',
                'params' => [],
            ],
            'bar' => [
                'path'      => 'ac-mailer/bar',
                'params'    => [],
                'children'  => [
                    'nested' => [
                        'path'      => 'ac-mailer/nested',
                        'params'    => [],
                    ]
                ]
            ]
        ];

        $this->templateOptions->setChildren($children);
        $this->recursiveChildAssert($this->templateOptions->getChildren());
    }

    private function recursiveChildAssert($children)
    {
        /* @var TemplateOptions $child */
        foreach ($children as $child) {
            $this->assertInstanceOf('AcMailer\Options\TemplateOptions', $child);
            $this->recursiveChildAssert($child->getChildren());
        }
    }

    public function testDefaultLayout()
    {
        $layoutConfig = [
            'path' => 'foo/bar/baz',
            'params' => [
                'foo' => 'bar'
            ],
            'template_capture_to' => 'content'
        ];
        $this->assertSame($this->templateOptions, $this->templateOptions->setDefaultLayout($layoutConfig));
        $this->assertEquals($layoutConfig, $this->templateOptions->getDefaultLayout());
    }
}
