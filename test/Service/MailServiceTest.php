<?php
declare(strict_types=1);

namespace AcMailerTest\Service;

use AcMailer\Attachment\AttachmentParserManagerInterface;
use AcMailer\Attachment\Parser\AttachmentParserInterface;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Exception\MailException;
use AcMailer\Exception\ServiceNotCreatedException;
use AcMailer\Model\Attachment;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Service\MailService;
use AcMailer\View\MailViewRendererInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ResponseCollection;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Part;

class MailServiceTest extends TestCase
{
    /**
     * @var MailService
     */
    private $mailService;
    /**
     * @var ObjectProphecy
     */
    private $transport;
    /**
     * @var ObjectProphecy
     */
    private $renderer;
    /**
     * @var ObjectProphecy
     */
    private $emailBuilder;
    /**
     * @var ObjectProphecy
     */
    private $eventManager;
    /**
     * @var ObjectProphecy
     */
    private $attachmentParsers;

    public function setUp()
    {
        $this->transport = $this->prophesize(TransportInterface::class);
        $this->renderer = $this->prophesize(MailViewRendererInterface::class);
        $this->emailBuilder = $this->prophesize(EmailBuilderInterface::class);
        $this->attachmentParsers = $this->prophesize(AttachmentParserManagerInterface::class);
        $this->eventManager = $this->prophesize(EventManagerInterface::class);

        $this->eventManager->setIdentifiers(Argument::cetera())->willReturn(null);

        $this->mailService = new MailService(
            $this->transport->reveal(),
            $this->renderer->reveal(),
            $this->emailBuilder->reveal(),
            $this->attachmentParsers->reveal(),
            $this->eventManager->reveal()
        );
    }

    /**
     * @test
     * @dataProvider provideInvalidEmails
     * @param $email
     */
    public function sendInvalidEmailThrowsException($email)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mailService->send($email);
    }

    public function provideInvalidEmails(): array
    {
        return [
            [null],
            [new \stdClass()],
            [50],
        ];
    }

    /**
     * @test
     * @dataProvider provideValidEmails
     * @param $email
     */
    public function validEmailIsProperlySent($email)
    {
        $buildEmail = $this->emailBuilder->build(Argument::cetera())->willReturn(new Email());
        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());

        $this->mailService->send($email);

        $buildEmail->shouldHaveBeenCalledTimes(\is_object($email) ? 0 : 1);
        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(3);
    }

    public function provideValidEmails(): array
    {
        return [
            ['the_email'],
            [[]],
            [new Email()],
        ];
    }

    /**
     * @test
     */
    public function exceptionIsThrownInCaseOfError()
    {
        $this->transport->send(Argument::type(Message::class))->willThrow(\Exception::class)
                                                              ->shouldBeCalled();
        $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection())
                                                             ->shouldBeCalled();

        $this->expectException(MailException::class);
        $this->mailService->send(new Email());
    }

    /**
     * @test
     */
    public function whenPreSendReturnsFalseEmailsSendingIsCancelled()
    {
        $collections = new ResponseCollection();
        $collections->add(0, false);

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn($collections);

        $this->mailService->send(new Email());

        $send->shouldNotHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     */
    public function attachListeners()
    {
        $listener = $this->prophesize(MailListenerInterface::class);

        $listener->attach(Argument::cetera())->shouldBeCalled();
        $listener->detach(Argument::cetera())->shouldBeCalled();

        $this->mailService->attachMailListener($listener->reveal());
        $this->mailService->detachMailListener($listener->reveal());
    }

    /**
     * @test
     */
    public function templateIsRendererIfProvided()
    {
        $expectedBody = '<p>rendering result</p>';

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());
        $render = $this->renderer->render(Argument::cetera())->willReturn($expectedBody);

        $result = $this->mailService->send((new Email())->setTemplate('some/template'));

        $this->assertEquals($expectedBody, $result->getEmail()->getBody());
        $send->shouldHaveBeenCalledTimes(1);
        $trigger->shouldHaveBeenCalled();
        $render->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function attachmentsAreProperlyAddedToMessage()
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
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());

        $this->mailService->send((new Email())->setAttachments($attachments));

        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalled();
        $hasStringParser->shouldHaveBeenCalled();
        $hasArrayParser->shouldHaveBeenCalled();
        $hasFooParser->shouldHaveBeenCalled();
        $getStringParser->shouldHaveBeenCalled();
        $getArrayParser->shouldHaveBeenCalled();
        $getFooParser->shouldHaveBeenCalled();
        $parse->shouldHaveBeenCalledTimes(\count($attachments));
    }

    /**
     * @test
     */
    public function attachmentsThrowExceptionWhenParserCannotBeFound()
    {
        $attachmentParser = $this->prophesize(AttachmentParserInterface::class);
        $parse = $attachmentParser->parse(Argument::cetera())->willReturn(new Part());

        $hasStringParser = $this->attachmentParsers->has('string')->willReturn(false);
        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());

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

    /**
     * @test
     */
    public function arrayAttachmentsWithSpecificKeysAreProperlyCast()
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
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());

        $this->mailService->send((new Email())->setAttachments($attachments));

        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalled();
        $hasFooParser->shouldHaveBeenCalled();
        $getFooParser->shouldHaveBeenCalled();
        $parse->shouldHaveBeenCalledTimes(\count($attachments));
    }

    /**
     * @test
     */
    public function templateIsRenderedBeforeEmailIsSent()
    {
        $expectedBody = '<p>rendering result</p>';
        $resp = new ResponseCollection();
        $resp->push(false);
        $email = (new Email())->setTemplate('some/template');
        $count = 0;

        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->will(function () use (
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

    /**
     * @test
     */
    public function customHeadersAreProperlyAddedToMessage()
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
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());

        $this->mailService->send($email);

        $buildEmail->shouldHaveBeenCalledTimes(\is_object($email) ? 0 : 1);
        $send->shouldHaveBeenCalled();
        $trigger->shouldHaveBeenCalledTimes(3);
    }
}
