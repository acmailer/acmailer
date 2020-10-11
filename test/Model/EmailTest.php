<?php

declare(strict_types=1);

namespace AcMailerTest\Model;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Model\Attachment;
use AcMailer\Model\Email;
use Exception;
use Laminas\Mime\Message;
use Laminas\Mime\Part;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_class;
use function gettype;
use function implode;
use function is_object;
use function sprintf;

class EmailTest extends TestCase
{
    private Email $email;

    public function setUp(): void
    {
        $this->email = new Email();
    }

    /**
     * @test
     * @dataProvider provideInvalidBodies
     */
    public function setBodyThrowsExceptionIfValueIsNotValid(?object $invalidBody): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(sprintf(
            'Provided body is not valid. Expected one of ["%s"], but "%s" was provided',
            implode('", "', ['string', Part::class, Message::class]),
            is_object($invalidBody) ? get_class($invalidBody) : gettype($invalidBody),
        ));
        $this->email->setBody($invalidBody);
    }

    public function provideInvalidBodies(): iterable
    {
        yield [null];
        yield [new stdClass()];
        yield [new Exception()];
    }

    /**
     * @test
     * @dataProvider provideInvalidAttachments
     */
    public function setInvalidAttachmentsThrowsException(array $invalidAttachments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(sprintf(
            'Provided attachment is not valid. Expected one of ["%s"], but',
            implode('", "', ['string', 'array', 'resource', Part::class, Attachment::class]),
        ));
        $this->email->setAttachments($invalidAttachments);
    }

    public function provideInvalidAttachments(): iterable
    {
        yield [['foo', null]];
        yield [[new stdClass()]];
        yield [[new Part(), 5]];
    }

    /** @test */
    public function providedNamesAreSavedForAttachments(): void
    {
        $this->email->addAttachments([
            'foo' => __FILE__,
        ]);

        $this->assertArrayHasKey('foo', $this->email->getAttachments());
    }

    /** @test */
    public function attachmentsAreProperlyComputed(): void
    {
        $this->email->addAttachment(__DIR__ . '/../../test-resources/attachments/file1');
        $this->email->addAttachment(__DIR__ . '/../../test-resources/attachments/file2');
        $this->email->setAttachmentsDir([
            'path' => __DIR__ . '/../../test-resources/attachments/dir',
            'recursive' => true,
        ]);

        $computed = $this->email->getComputedAttachments();

        $this->assertCount(4, $computed);
    }

    /**
     * @test
     * @dataProvider provideEmailsWithAttachments
     */
    public function hasAttachmentsReturnsExpectedValue(Email $email, bool $expected): void
    {
        $this->assertEquals($expected, $email->hasAttachments());
    }

    public function provideEmailsWithAttachments(): iterable
    {
        yield [new Email(), false];
        yield [(new Email())->setAttachments([__FILE__]), true];
        yield [(new Email())->setAttachmentsDir(['path' => __DIR__]), true];
        yield [(new Email())->setAttachments([__FILE__])->setAttachmentsDir(['path' => __DIR__]), true];
    }
}
