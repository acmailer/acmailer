<?php

declare(strict_types=1);

namespace AcMailerTest\Service;

use AcMailer\Attachment\AttachmentParserManagerInterface;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use AcMailer\Event\DispatchResult;
use AcMailer\Event\EventDispatcherInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Event\SendErrorEvent;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Exception\MailException;
use AcMailer\Exception\ServiceNotCreatedException;
use AcMailer\Model\Attachment;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Service\MailService;
use AcMailer\View\MailViewRendererInterface;
use Exception;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Part;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use stdClass;

use function count;
use function is_object;

class MailServiceTest extends TestCase
{
    use ProphecyTrait;

    private MailService $mailService;
    private ObjectProphecy $transport;
    private ObjectProphecy $renderer;
    private ObjectProphecy $emailBuilder;
    private ObjectProphecy $eventDispatcher;
    private ObjectProphecy $attachmentParsers;

    public function setUp(): void
    {
        $this->transport = $this->prophesize(TransportInterface::class);
        $this->renderer = $this->prophesize(MailViewRendererInterface::class);
        $this->emailBuilder = $this->prophesize(EmailBuilderInterface::class);
        $this->attachmentParsers = $this->prophesize(AttachmentParserManagerInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->mailService = new MailService(
            $this->transport->reveal(),
            $this->renderer->reveal(),
            $this->emailBuilder->reveal(),
            $this->attachmentParsers->reveal(),
            $this->eventDispatcher->reveal(),
        );
    }

    /**
     * @test
     * @dataProvider provideInvalidEmails
     * @param mixed $email
     */
    public function sendInvalidEmailThrowsException($email): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mailService->send($email);
    }

    public function provideInvalidEmails(): iterable
    {
        yield [null];
        yield [new stdClass()];
        yield [50];
    }

    /**
     * @test
     * @dataProvider provideValidEmails
     * @param mixed $email
     */
    public function validEmailIsProperlySent($email): void
    {
        $buildEmail = $this->emailBuilder->build(Argument::cetera())->willReturn(new Email());
        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());

        $this->mailService->send($email);

        $buildEmail->shouldHaveBeenCalledTimes(is_object($email) ? 0 : 1);
        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(3);
    }

    public function provideValidEmails(): iterable
    {
        yield ['the_email'];
        yield [[]];
        yield [new Email()];
    }

    /** @test */
    public function exceptionIsThrownInCaseOfError(): void
    {
        $throwable = new RuntimeException('An error occured');

        $this->transport->send(Argument::type(Message::class))->willThrow($throwable)
                                                              ->shouldBeCalledOnce();
        $this->eventDispatcher->dispatch(Argument::that(function ($e) use ($throwable) {
            if ($e instanceof SendErrorEvent) {
                Assert::assertFalse($e->getResult()->isValid());
                Assert::assertEquals($throwable, $e->getResult()->getThrowable());
            }

            return $e;
        }))->willReturn(new DispatchResult())->shouldBeCalledTimes(3);

        $this->expectException(MailException::class);

        $this->mailService->send(new Email());
    }

    /** @test */
    public function whenPreSendReturnsFalseEmailsSendingIsCancelled(): void
    {
        $dispatchResult = new DispatchResult();
        $dispatchResult->add(0, false);

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn($dispatchResult);

        $result = $this->mailService->send(new Email());

        $this->assertFalse($result->isValid());
        $send->shouldNotHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(2);
    }

    /** @test */
    public function attachListeners(): void
    {
        $listener = $this->prophesize(MailListenerInterface::class)->reveal();

        $attachOnDispatcher = $this->eventDispatcher->attachMailListener($listener, 1);
        $detachMailListener = $this->eventDispatcher->detachMailListener($listener);

        $this->mailService->attachMailListener($listener);
        $this->mailService->detachMailListener($listener);

        $attachOnDispatcher->shouldHaveBeenCalledOnce();
        $detachMailListener->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function templateIsRenderedIfProvided(): void
    {
        $expectedBody = '<p>rendering result</p>';

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());
        $render = $this->renderer->render(Argument::cetera())->willReturn($expectedBody);

        $result = $this->mailService->send((new Email())->setTemplate('some/template'));

        $this->assertEquals($expectedBody, $result->getEmail()->getBody());
        $send->shouldHaveBeenCalledTimes(1);
        $trigger->shouldHaveBeenCalled();
        $render->shouldHaveBeenCalledTimes(1);
    }

    /** @test */
    public function attachmentsAreProperlyAddedToMessage(): void
    {
        $attachments = ['', '', '', [], new Attachment('foo', 'value')];

        $attachmentParser = $this->prophesize(AttachmentParserInterface::class);
        $parse = $attachmentParser->parse(Argument::cetera())->willReturn(new Part());

        $hasStringParser = $this->attachmentParsers->has('string')->willReturn(true);
        $hasArrayParser = $this->attachmentParsers->has('array')->willReturn(true);
        $hasFooParser = $this->attachmentParsers->has('foo')->willReturn(true);
        $getStringParser = $this->attachmentParsers->get('string')->willReturn($attachmentParser->reveal());
        $getArrayParser = $this->attachmentParsers->get('array')->willReturn($attachmentParser->reveal());
        $getFooParser = $this->attachmentParsers->get('foo')->willReturn($attachmentParser->reveal());

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());

        $this->mailService->send((new Email())->setAttachments($attachments));

        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalled();
        $hasStringParser->shouldHaveBeenCalled();
        $hasArrayParser->shouldHaveBeenCalled();
        $hasFooParser->shouldHaveBeenCalled();
        $getStringParser->shouldHaveBeenCalled();
        $getArrayParser->shouldHaveBeenCalled();
        $getFooParser->shouldHaveBeenCalled();
        $parse->shouldHaveBeenCalledTimes(count($attachments));
    }

    /** @test */
    public function attachmentsThrowExceptionWhenParserCannotBeFound(): void
    {
        $attachmentParser = $this->prophesize(AttachmentParserInterface::class);
        $parse = $attachmentParser->parse(Argument::cetera())->willReturn(new Part());

        $hasStringParser = $this->attachmentParsers->has('string')->willReturn(false);
        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());

        try {
            $this->mailService->send((new Email())->setAttachments(['']));
        } catch (MailException $e) {
            $this->assertInstanceOf(ServiceNotCreatedException::class, $e->getPrevious());
            $this->assertEquals('The attachment parser "string" could not be found', $e->getPrevious()->getMessage());
        }

        $send->shouldNotHaveBeenCalled();
        $trigger->shouldHaveBeenCalled();
        $hasStringParser->shouldHaveBeenCalled();
        $parse->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function arrayAttachmentsWithSpecificKeysAreProperlyCast(): void
    {
        $attachments = [[
            'parser_name' => 'foo',
            'value' => 'the_value',
        ]];

        $attachmentParser = $this->prophesize(AttachmentParserInterface::class);
        $parse = $attachmentParser->parse('the_value', null)->willReturn(new Part());

        $hasFooParser = $this->attachmentParsers->has('foo')->willReturn(true);
        $getFooParser = $this->attachmentParsers->get('foo')->willReturn($attachmentParser->reveal());

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());

        $this->mailService->send((new Email())->setAttachments($attachments));

        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalled();
        $hasFooParser->shouldHaveBeenCalled();
        $getFooParser->shouldHaveBeenCalled();
        $parse->shouldHaveBeenCalledTimes(count($attachments));
    }

    /** @test */
    public function templateIsRenderedBeforeEmailIsSent(): void
    {
        $expectedBody = '<p>rendering result</p>';
        $resp = new DispatchResult();
        $resp->push(false);
        $email = (new Email())->setTemplate('some/template');
        $count = 0;

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->will(function () use (
            $email,
            $resp,
            &$count
        ) {
            if ($count === 0) {
                // On first event (pre-render), the body is still empty.
                Assert::assertEmpty($email->getBody());
            } else {
                // On second event (pre-send), the template has already been rendered and set in the email body.
                Assert::assertNotEmpty($email->getBody());
            }

            $count++;

            return $resp;
        });
        $render = $this->renderer->render(Argument::cetera())->willReturn($expectedBody);

        $result = $this->mailService->send($email);

        $this->assertEquals($expectedBody, $result->getEmail()->getBody());
        $send->shouldNotHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(2);
        $render->shouldHaveBeenCalledTimes(1);
    }

    /** @test */
    public function customHeadersAreProperlyAddedToMessage(): void
    {
        $email = new Email();
        $email->setCustomHeaders([
            'foo' => 'bar',
            'baz' => 'foo',
        ]);
        $email->addCustomHeader('something', 'else');

        $buildEmail = $this->emailBuilder->build(Argument::cetera())->willReturn(new Email());
        $send = $this->transport->send(Argument::that(function (Message $message) {
            $headers = $message->getHeaders()->toArray();
            Assert::assertArrayHasKey('Foo', $headers);
            Assert::assertEquals('bar', $headers['Foo']);
            Assert::assertArrayHasKey('Baz', $headers);
            Assert::assertEquals('foo', $headers['Baz']);
            Assert::assertArrayHasKey('Something', $headers);
            Assert::assertEquals('else', $headers['Something']);

            return $message;
        }))->willReturn(null);
        $trigger = $this->eventDispatcher->dispatch(Argument::cetera())->willReturn(new DispatchResult());

        $this->mailService->send($email);

        $buildEmail->shouldHaveBeenCalledTimes(is_object($email) ? 0 : 1);
        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(3);
    }
}
