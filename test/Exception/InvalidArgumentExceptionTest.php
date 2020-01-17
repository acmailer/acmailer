<?php

declare(strict_types=1);

namespace AcMailerTest\Exception;

use AcMailer\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class InvalidArgumentExceptionTest extends TestCase
{
    /**
     * @param mixed $value
     * @test
     * @dataProvider provideExceptionData
     */
    public function exceptionMessageIsProperlyBuilt(
        array $types,
        $value,
        string $fieldName,
        string $expectedMessage
    ): void {
        $e = InvalidArgumentException::fromValidTypes($types, $value, $fieldName);

        $this->assertInstanceOf(InvalidArgumentException::class, $e);
        $this->assertEquals($expectedMessage, $e->getMessage());
    }

    public function provideExceptionData(): iterable
    {
        yield [
            ['foo', 'bar'],
            new stdClass(),
            'the_field',
            'Provided the_field is not valid. Expected one of ["foo", "bar"], but "stdClass" was provided',
        ];
        yield [
            ['hello_world'],
            1,
            'the_field2',
            'Provided the_field2 is not valid. Expected one of ["hello_world"], but "integer" was provided',
        ];
        yield [
            [1, 2, 3],
            'foo',
            'the_field3',
            'Provided the_field3 is not valid. Expected one of ["1", "2", "3"], but "string" was provided',
        ];
    }
}
