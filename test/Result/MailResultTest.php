<?php
namespace AcMailerTest\Result;

use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

/**
 * Mail result test case
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailResultTest extends TestCase
{
    /**
     * @var ResultInterface
     */
    private $mailResult;

    /**
     * @test
     */
    public function defaultValuesAreApplied()
    {
        $email = new Email();

        $this->mailResult = new MailResult($email);

        $this->assertTrue($this->mailResult->isValid());
        $this->assertFalse($this->mailResult->isCancelled());
        $this->assertSame($email, $this->mailResult->getEmail());
        $this->assertFalse($this->mailResult->hasException());
        $this->assertNull($this->mailResult->getException());
    }

    /**
     * @test
     * @dataProvider provideResultData
     * @param bool $isValid
     * @param \Throwable|null $e
     */
    public function customValuesAreApplied(bool $isValid, \Throwable $e = null)
    {
        $this->mailResult = new MailResult(new Email(), $isValid, $e);

        $this->assertEquals($isValid, $this->mailResult->isValid());
        $this->assertEquals($e !== null, $this->mailResult->hasException());
        $this->assertEquals($e, $this->mailResult->getException());
        $this->assertEquals(! $isValid && $e === null, $this->mailResult->isCancelled());
    }

    public function provideResultData(): array
    {
        return [
            [true, null],
            [false, null],
            [false, new \Exception()],
        ];
    }

    /**
     * @test
     * @dataProvider provideExceptions
     * @param bool $hasException
     * @param \Throwable|null $e
     */
    public function exceptionReturnsExpectedValue(bool $hasException, \Throwable $e = null)
    {
        $this->mailResult = new MailResult(new Email(), false, $e);

        $this->assertEquals($hasException, $this->mailResult->hasException());
        $this->assertEquals($e, $this->mailResult->getException());
    }

    public function provideExceptions(): array
    {
        return [
            [true, new \Exception()],
            [false, null],
        ];
    }
}
