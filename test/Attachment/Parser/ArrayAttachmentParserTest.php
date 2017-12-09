<?php
declare(strict_types=1);

namespace AcMailerTest\Attachment\Parser;

use AcMailer\Attachment\Parser\ArrayAttachmentParser;
use AcMailer\Exception\InvalidAttachmentException;
use PHPUnit\Framework\TestCase;
use Zend\Mime\Mime;

class ArrayAttachmentParserTest extends TestCase
{
    /**
     * @var ArrayAttachmentParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new ArrayAttachmentParser();
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfAttachmentHasInvalidType()
    {
        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Provided attachment is not valid. Expected "array" to be passed');
        $this->parser->parse('');
    }

    /**
     * @test
     */
    public function providedAttachmentIsParsedIntoPart()
    {
        $attachment = [
            'id' => 'something',
            'filename' => 'something_else',
            'content' => 'Hello',
            'encoding' => Mime::ENCODING_7BIT,
        ];

        $part = $this->parser->parse($attachment);

        $this->assertEquals($part->id, 'something');
        $this->assertEquals($part->filename, 'something_else');
        $this->assertEquals($part->getContent(), 'Hello');
        $this->assertEquals($part->encoding, Mime::ENCODING_7BIT);
        $this->assertEquals($part->disposition, Mime::DISPOSITION_ATTACHMENT);
    }

    /**
     * @test
     */
    public function idAndNameAreOverriddenIfNameIsProvided()
    {
        $attachment = [
            'id' => 'something',
            'filename' => 'something_else',
        ];

        $part = $this->parser->parse($attachment, 'the_name');

        $this->assertEquals($part->id, 'the_name');
        $this->assertEquals($part->filename, 'the_name');
    }
}
