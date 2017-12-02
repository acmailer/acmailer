<?php
declare(strict_types=1);

namespace AcMailerTest\Model;

use AcMailer\Exception\EmailNotFoundException;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilder;
use PHPUnit\Framework\TestCase;

class EmailBuilderTest extends TestCase
{
    /**
     * @var EmailBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new EmailBuilder([
            'an_email' => [
                'fromName' => 'foobar',
                'cc' => [
                    'foo@bar.com',
                ],
            ],
            'another_email' => [
                'fromName' => 'something',
                'bcc' => [
                    'foo@bar.com',
                ],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider provideEmails
     * @param string $emailName
     * @param string $expectedFromName
     * @param array $options
     */
    public function requestedEmailIsProperlyBuildWhenFound(
        string $emailName,
        string $expectedFromName,
        array $options = []
    ) {
        $email = $this->builder->build($emailName, $options);
        $this->assertEquals($expectedFromName, $email->getFromName());
    }

    public function provideEmails(): array
    {
        return [
            'an_email' => ['an_email', 'foobar'],
            'another_email' => ['another_email', 'something'],
            'another_email with option overridden' => ['another_email', 'overridden', ['fromName' => 'overridden']],
            'default email' => [Email::class, ''],
            'default email with overridden value' => [Email::class, 'me', ['fromName' => 'me']],
        ];
    }

    /**
     * @test
     */
    public function exceptionIsThrownWhenInvalidEmailIsRequested()
    {
        $this->expectException(EmailNotFoundException::class);
        $this->expectExceptionMessage('An email with name "invalid" could not be found in registered emails list');
        $this->builder->build('invalid');
    }
}
