<?php
declare(strict_types=1);

namespace AcMailerTest\Service;

use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Exception\MailException;
use AcMailer\Model\Email;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Service\MailService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ResponseCollection;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;

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

    public function setUp()
    {
        $this->transport = $this->prophesize(TransportInterface::class);
        $this->renderer = $this->prophesize(TemplateRendererInterface::class);
        $this->emailBuilder = $this->prophesize(EmailBuilderInterface::class);
        $this->eventManager = $this->prophesize(EventManagerInterface::class);

        $this->eventManager->setIdentifiers(Argument::cetera())->willReturn(null);

        $this->mailService = new MailService(
            $this->transport->reveal(),
            $this->renderer->reveal(),
            $this->emailBuilder->reveal(),
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
        $trigger->shouldHaveBeenCalledTimes(2);
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
        $trigger->shouldHaveBeenCalledTimes(1);
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
        $send = $this->transport->send(Argument::type(Message::class))->willReturn(null);
        $trigger = $this->eventManager->triggerEvent(Argument::cetera())->willReturn(new ResponseCollection());
        $render = $this->renderer->render(Argument::cetera())->willReturn('');

        $this->mailService->send((new Email())->setTemplate('some/template'));

        $send->shouldHaveBeenCalledTimes(1);
        $trigger->shouldHaveBeenCalled();
        $render->shouldHaveBeenCalledTimes(1);
    }
}
