<?php

declare(strict_types=1);

namespace AcMailerTest\Result;

use AcMailer\Model\Email;
use AcMailer\Result\MailResult;
use AcMailer\Result\ResultInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

class MailResultTest extends TestCase
{
    private ResultInterface $mailResult;

    /**
     * @test
     */
    public function defaultValuesAreApplied(): void
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
     */
    public function customValuesAreApplied(bool $isValid, ?Throwable $e = null): void
    {
        $this->mailResult = new MailResult(new Email(), $isValid, $e);

        $this->assertEquals($isValid, $this->mailResult->isValid());
        $this->assertEquals($e !== null, $this->mailResult->hasException());
        $this->assertEquals($e, $this->mailResult->getException());
        $this->assertEquals(! $isValid && $e === null, $this->mailResult->isCancelled());
    }

    public function provideResultData(): iterable
    {
        yield [true, null];
        yield [false, null];
        yield [false, new Exception()];
    }

    /**
     * @test
     * @dataProvider provideExceptions
     */
    public function exceptionReturnsExpectedValue(bool $hasException, ?Throwable $e = null): void
    {
        $this->mailResult = new MailResult(new Email(), false, $e);

        $this->assertEquals($hasException, $this->mailResult->hasException());
        $this->assertEquals($e, $this->mailResult->getException());
    }

    public function provideExceptions(): iterable
    {
        yield [true, new Exception()];
        yield [false, null];
    }
}
