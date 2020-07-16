<?php

declare(strict_types=1);

namespace AcMailerTest\Attachment\Parser;

use AcMailer\Attachment\Parser\FilePathAttachmentParser;
use AcMailer\Exception\InvalidAttachmentException;
use finfo;
use Laminas\Mime\Mime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use function basename;

class FilePathAttachmentParserTest extends TestCase
{
    use ProphecyTrait;

    private FilePathAttachmentParser $parser;
    private ObjectProphecy $finfo;

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
     * @test
     * @dataProvider provideAttachmentNames
     */
    public function providedAttachmentIsParsedIntoPart(?string $attachmentName = null): void
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

    public function provideAttachmentNames(): iterable
    {
        yield [null];
        yield ['the_name'];
    }
}
