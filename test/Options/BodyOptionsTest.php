<?php
namespace AcMailerTest\Options;

use AcMailer\Options\BodyOptions;
use AcMailer\Options\TemplateOptions;
use AcMailer\Service\MailServiceInterface;
use PHPUnit_Framework_TestCase as TestCase;

class BodyOptionsTest extends TestCase
{
    /**
     * @var BodyOptions
     */
    private $bodyOptions;

    public function setUp()
    {
        $this->bodyOptions = new BodyOptions();
    }

    public function testDefaultValue()
    {
        $this->assertFalse($this->bodyOptions->getUseTemplate());
        $this->assertEquals('', $this->bodyOptions->getContent());
        $this->assertEquals(MailServiceInterface::DEFAULT_CHARSET, $this->bodyOptions->getCharset());
        $this->assertInstanceOf('AcMailer\Options\TemplateOptions', $this->bodyOptions->getTemplate());
    }

    public function testSetCharset()
    {
        $expected = 'CP-1252';
        $this->assertSame($this->bodyOptions, $this->bodyOptions->setCharset($expected));
        $this->assertEquals($expected, $this->bodyOptions->getCharset());
    }

    public function testSetTemplate()
    {
        $expected = new TemplateOptions();
        $this->assertSame($this->bodyOptions, $this->bodyOptions->setTemplate($expected));
        $this->assertSame($expected, $this->bodyOptions->getTemplate());

        $this->bodyOptions->setTemplate([]);
        $this->assertInstanceOf('AcMailer\Options\TemplateOptions', $this->bodyOptions->getTemplate());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testSetTemplateWithInvalidValueThrowsException()
    {
        $this->bodyOptions->setTemplate('foobar');
    }
}
