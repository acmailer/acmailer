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
        $this->assertEquals(array(), $this->templateOptions->getChilds());
        $this->assertCount(0, $this->templateOptions->getChilds());
    }

    public function testChildsCastToTemplateOptions()
    {
        $childs = array(
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
                'childs'    => array(
                    'nested' => array(
                        'path'      => 'ac-mailer/nested',
                        'params'    => array(),
                    )
                )
            )
        );

        $this->templateOptions->setChilds($childs);
        $this->recursiveChildAssert($this->templateOptions->getChilds());
    }

    private function recursiveChildAssert($childs)
    {
        /* @var TemplateOptions $child */
        foreach ($childs as $child) {
            $this->assertInstanceOf('AcMailer\Options\TemplateOptions', $child);
            $this->recursiveChildAssert($child->getChilds());
        }
    }

} 