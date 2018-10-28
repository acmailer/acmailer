<?php
declare(strict_types=1);

namespace AcMailerTest\Model;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Model\Email;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Mime\Part;

class EmailTest extends TestCase
{
    /**
     * @var Email
     */
    private $email;

    public function setUp()
    {
        $this->email = new Email();
    }

    /**
     * @param $invalidBody
     * @test
     * @dataProvider provideInvalidBodies
     */
    public function setBodyThrowsExceptionIfValueIsNotValid($invalidBody)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->email->setBody($invalidBody);
    }

    public function provideInvalidBodies(): array
    {
        return [
            [null],
            [new stdClass()],
            [new Exception()],
        ];
    }

    /**
     * @param array $invalidAttachments
     * @test
     * @dataProvider provideInvalidAttachments
     */
    public function setInvalidAttachmentsThrowsException(array $invalidAttachments)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->email->setAttachments($invalidAttachments);
    }

    public function provideInvalidAttachments(): array
    {
        return [
            [['foo', null]],
            [[new stdClass()]],
            [[new Part(), 5]],
        ];
    }

    /**
     * @test
     */
    public function providedNamesAreSavedForAttachments()
    {
        $this->email->addAttachments([
            'foo' => __FILE__,
        ]);

        $this->assertArrayHasKey('foo', $this->email->getAttachments());
    }

    /**
     * @test
     */
    public function attachmentsAreProperlyComputed()
    {
        $this->email->addAttachment(__DIR__ . '/../attachments/file1');
        $this->email->addAttachment(__DIR__ . '/../attachments/file2');
        $this->email->setAttachmentsDir([
            'path' => __DIR__ . '/../attachments/dir',
            'recursive' => true,
        ]);

        $computed = $this->email->getComputedAttachments();

        $this->assertCount(4, $computed);
    }
}
