<?php

declare(strict_types=1);

namespace AcMailerTest\Attachment\Parser;

use AcMailer\Attachment\Parser\MimePartAttachmentParser;
use AcMailer\Exception\InvalidAttachmentException;
use PHPUnit\Framework\TestCase;
use Laminas\Mime\Part;

use function sprintf;

class MimePartAttachmentParserTest extends TestCase
{
    /** @var MimePartAttachmentParser */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new MimePartAttachmentParser();
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfAttachmentHasInvalidType(): void
    {
        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage(
            sprintf('Provided attachment is not valid. Expected "%s"', Part::class)
        );
        $this->parser->parse('');
    }

    /**
     * @param string|null $attachmentName
     * @test
     * @dataProvider provideAttachmentNames
     */
    public function providedPartIsReturned(?string $attachmentName = null): void
    {
        $part = new Part();

        $result = $this->parser->parse($part, $attachmentName);

        $this->assertSame($part, $result);
        $this->assertEquals($part->id, $attachmentName);
        $this->assertEquals($part->filename, $attachmentName);
    }

    public function provideAttachmentNames(): iterable
    {
        yield [null];
        yield ['the_name'];
    }
}
