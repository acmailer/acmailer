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
    }

} 