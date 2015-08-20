<?php
namespace AcMailerTest\Options;

use AcMailer\Options\AttachmentsOptions;
use AcMailer\Options\BodyOptions;
use AcMailer\Options\MessageOptions;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MessageOptionsTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MessageOptionsTest extends TestCase
{
    /**
     * @var MessageOptions
     */
    protected $messageOptions;

    public function setUp()
    {
        $this->messageOptions = new MessageOptions();
    }

    public function testDefaultValues()
    {
        $this->assertEquals('', $this->messageOptions->getFrom());
        $this->assertEquals('', $this->messageOptions->getFromName());
        $this->assertEquals('', $this->messageOptions->getReplyTo());
        $this->assertEquals('', $this->messageOptions->getReplyToName());
        $this->assertEquals([], $this->messageOptions->getTo());
        $this->assertEquals([], $this->messageOptions->getCc());
        $this->assertEquals([], $this->messageOptions->getBcc());
        $this->assertEquals('', $this->messageOptions->getSubject());
        $this->assertInstanceOf('AcMailer\Options\BodyOptions', $this->messageOptions->getBody());
        $this->assertInstanceOf('AcMailer\Options\AttachmentsOptions', $this->messageOptions->getAttachments());
    }

    public function testSetBody()
    {
        $expected = new BodyOptions();
        $this->assertSame($this->messageOptions, $this->messageOptions->setBody($expected));
        $this->assertSame($expected, $this->messageOptions->getBody());

        $this->messageOptions->setBody([]);
        $this->assertInstanceOf('AcMailer\Options\BodyOptions', $this->messageOptions->getBody());
    }

    public function testSetAttachments()
    {
        $expected = new AttachmentsOptions();
        $this->assertSame($this->messageOptions, $this->messageOptions->setAttachments($expected));
        $this->assertSame($expected, $this->messageOptions->getAttachments());

        $this->messageOptions->setAttachments([]);
        $this->assertInstanceOf('AcMailer\Options\AttachmentsOptions', $this->messageOptions->getAttachments());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testInvalidBodyThrowsException()
    {
        $this->messageOptions->setBody(new \stdClass());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testInvalidAttachmentsThrowException()
    {
        $this->messageOptions->setAttachments('foo');
    }
}
