<?php
declare(strict_types=1);

namespace AcMailerTest\Attachment\Parser;

use AcMailer\Attachment\Parser\ResourceAttachmentParser;
use AcMailer\Exception\InvalidAttachmentException;
use PHPUnit\Framework\TestCase;
use Zend\Mime\Mime;
use function fopen;

class ResourceAttachmentParserTest extends TestCase
{
    /** @var ResourceAttachmentParser */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new ResourceAttachmentParser();
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfAttachmentHasInvalidType(): void
    {
        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Provided attachment is not valid. Expected "resource"');
        $this->parser->parse('');
    }

    /**
     * @param string|null $attachmentName
     * @test
     * @dataProvider provideAttachmentNames
     */
    public function providedAttachmentIsParsedIntoPart(string $attachmentName = null): void
    {
        $attachment = fopen(__DIR__ . '/../../../test-resources/attachments/file2', 'rb');

        $part = $this->parser->parse($attachment, $attachmentName);

        $this->assertEquals($part->id, $attachmentName ?? 'file2');
        $this->assertEquals($part->filename, $attachmentName ?? 'file2');
        $this->assertEquals($part->encoding, Mime::ENCODING_BASE64);
        $this->assertEquals($part->disposition, Mime::DISPOSITION_ATTACHMENT);
    }

    public function provideAttachmentNames(): iterable
    {
        yield [null];
        yield ['the_name'];
    }
}
