<?php
declare(strict_types=1);

namespace AcMailerTest\Attachment\Parser;

use AcMailer\Attachment\Parser\FilePathAttachmentParser;
use AcMailer\Exception\InvalidAttachmentException;
use finfo;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Mime\Mime;
use function basename;

class FilePathAttachmentParserTest extends TestCase
{
    /** @var FilePathAttachmentParser */
    private $parser;
    /** @var ObjectProphecy */
    private $finfo;

    public function setUp(): void
    {
        $this->finfo = $this->prophesize(finfo::class);
        $this->parser = new FilePathAttachmentParser($this->finfo->reveal());
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfAttachmentHasInvalidType(): void
    {
        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Provided attachment is not valid. Expected "file path"');
        $this->parser->parse('');
    }

    /**
     * @param string|null $attachmentName
     * @test
     * @dataProvider provideAttachmentNames
     */
    public function providedAttachmentIsParsedIntoPart(string $attachmentName = null): void
    {
        $attachment = __DIR__ . '/../../../test-resources/attachments/file1';

        $getMimeType = $this->finfo->file($attachment)->willReturn('text/plain');

        $part = $this->parser->parse($attachment, $attachmentName);

        $this->assertEquals($part->type, 'text/plain');
        $this->assertEquals($part->id, $attachmentName ?? basename($attachment));
        $this->assertEquals($part->filename, $attachmentName ?? basename($attachment));
        $this->assertEquals($part->encoding, Mime::ENCODING_BASE64);
        $this->assertEquals($part->disposition, Mime::DISPOSITION_ATTACHMENT);
        $getMimeType->shouldHaveBeenCalled();
    }

    public function provideAttachmentNames(): array
    {
        return [
            [null],
            ['the_name'],
        ];
    }
}
