<?php
namespace AcMailerTest\Options;

use AcMailer\Options\TemplateOptions;

class TemplateOptionsTest extends \PHPUnit_Framework_TestCase
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
        $this->assertFalse($this->templateOptions->getUseTemplate());
        $this->assertEquals('ac-mailer/mail-templates/mail', $this->templateOptions->getPath());
        $this->assertEquals(array(), $this->templateOptions->getParams());
        $this->assertCount(0, $this->templateOptions->getParams());
        $this->assertEquals(array(), $this->templateOptions->getChildren());
        $this->assertCount(0, $this->templateOptions->getChildren());
    }

    public function testChildrenCastToTemplateOptions()
    {
        $children = array(
            'content' => array(
                'path'   => 'ac-mailer/content',
                'params' => array(),
            ),
            'foo' => array(
                'path'   => 'ac-mailer/foo',
                'params' => array(),
            ),
            'bar' => array(
                'path'      => 'ac-mailer/bar',
                'params'    => array(),
                'children'  => array(
                    'nested' => array(
                        'path'      => 'ac-mailer/nested',
                        'params'    => array(),
                    )
                )
            )
        );

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
}
