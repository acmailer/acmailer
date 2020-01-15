<?php

declare(strict_types=1);

namespace AcMailerTest\Model;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Model\Email;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Laminas\Mime\Part;

class EmailTest extends TestCase
{
    /** @var Email */
    private $email;

    public function setUp(): void
    {
        $this->email = new Email();
    }

    /**
     * @param $invalidBody
     * @test
     * @dataProvider provideInvalidBodies
     */
    public function setBodyThrowsExceptionIfValueIsNotValid($invalidBody): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->email->setBody($invalidBody);
    }

    public function provideInvalidBodies(): iterable
    {
        yield [null];
        yield [new stdClass()];
        yield [new Exception()];
    }

    /**
     * @param array $invalidAttachments
     * @test
     * @dataProvider provideInvalidAttachments
     */
    public function setInvalidAttachmentsThrowsException(array $invalidAttachments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->email->setAttachments($invalidAttachments);
    }

    public function provideInvalidAttachments(): iterable
    {
        yield [['foo', null]];
        yield [[new stdClass()]];
        yield [[new Part(), 5]];
    }

    /**
     * @test
     */
    public function providedNamesAreSavedForAttachments(): void
    {
        $this->email->addAttachments([
            'foo' => __FILE__,
        ]);

        $this->assertArrayHasKey('foo', $this->email->getAttachments());
    }

    /**
     * @test
     */
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
}
