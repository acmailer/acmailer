<?php

declare(strict_types=1);

namespace AcMailerTest\Service\Factory;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Model\EmailBuilder;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailService;
use AcMailer\View\MailViewRendererInterface;
use AcMailer\View\MezzioMailViewRenderer;
use AcMailer\View\MvcMailViewRenderer;
use Interop\Container\ContainerInterface;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionObject;
use stdClass;

use function implode;
use function sprintf;

class MailServiceAbstractFactoryTest extends TestCase
{
    use ProphecyTrait;

    private MailServiceAbstractFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->factory = new MailServiceAbstractFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideServiceNames
     */
    public function canCreateReturnsProperResult(string $requestedName, bool $expected): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [],
                ],
            ],
        ]);

        $result = $this->factory->canCreate($this->container->reveal(), $requestedName);
        $this->assertEquals($expected, $result);
    }

    public function provideServiceNames(): iterable
    {
        yield ['invalid', false];
        yield ['acmailer.mailservice', false];
        yield ['foo.mailservice.invalid', false];
        yield ['acmailer.foo.bar', false];
        yield ['foo.bar.baz', false];
        yield ['acmailer.mailservice.invalid', false];
        yield ['acmailer.mailservice.default', true];
    }

    /** @test */
    public function exceptionIsThrownIfRequestedConfigIsNotFound(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [],
                ],
            ],
        ]);

        $this->expectException(Exception\ServiceNotCreatedException::class);
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.invalid');
    }

    /**
     * @test
     * @dataProvider provideValidServiceConfig
     */
    public function serviceIsCreatedIfConfigIsCorrect(array $config): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => $config,
                ],
            ],
        ]);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $this->container->get('my_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);
    }

    public function provideValidServiceConfig(): iterable
    {
        yield [[
            'transport' => 'sendmail',
        ]];
        yield [[
            'transport' => new Smtp(),
        ]];
        yield [[
            'transport' => new Smtp(),
            'renderer' => 'my_renderer',
        ]];
    }

    /** @test */
    public function standardTransportAsServiceIsKeptAsIs(): void
    {
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $transportServiceName = 'custom.mail.transport';
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => $transportServiceName,
                    ],
                ],
            ],
        ]);

        $transport = $this->prophesize(Smtp::class);
        $setTransportOptions = $transport->setOptions(Argument::any())->willReturn($transport->reveal());
        $this->container->has($transportServiceName)->willReturn(true);
        $this->container->get($transportServiceName)->willReturn($transport->reveal());

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);
        $setTransportOptions->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider provideInvalidTransports
     * @param mixed $transport
     */
    public function exceptionIsThrownIfConfiguredTransportHasAnInvalidValue(
        $transport,
        bool $inContainer,
        string $expectedMessage
    ): void {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => $transport,
                    ],
                ],
            ],
        ]);
        $this->container->has($transport)->willReturn($inContainer);
        if ($inContainer) {
            $this->container->get($transport)->willReturn(new stdClass());
        }

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    public function provideInvalidTransports(): iterable
    {
        yield [
            new stdClass(),
            false,
            sprintf(
                'Provided transport is not valid. Expected one of ["string", "%s"], but "%s" was provided',
                TransportInterface::class,
                stdClass::class,
            ),
        ];
        yield [800, false, sprintf(
            'Provided transport is not valid. Expected one of ["string", "%s"], but "integer" was provided',
            TransportInterface::class,
        )];
        yield ['my_transport', false, sprintf(
            'Registered transport "my_transport" is not either one of ["sendmail", "smtp", "file", "in_memory", "null"]'
            . ', a "%s" subclass or a registered service.',
            TransportInterface::class,
        )];
    }

    /** @test */
    public function wrongCustomRendererThrowsException(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => 'sendmail',
                        'renderer' => 'foo_renderer',
                    ],
                ],
            ],
        ]);
        $this->container->get('foo_renderer')->willReturn(new stdClass());

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Defined renderer of type "%s" is not valid. The renderer must resolve to a instance of ["%s"] types',
            stdClass::class,
            implode(
                '", "',
                [MailViewRendererInterface::class, TemplateRendererInterface::class, RendererInterface::class],
            ),
        ));
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /** @test */
    public function recursiveLoopOnExtendsThrowsException(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => 'sendmail',
                        'extends' => 'another',
                    ],
                    'another' => [
                        'extends' => 'foo',
                    ],
                    'foo' => [
                        'extends' => 'default',
                    ],
                ],
            ],
        ]);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(RendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );

        $this->expectException(Exception\ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            "It wasn't possible to create a mail service due to circular inheritance. Review 'extends' option.",
        );
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /** @test */
    public function extendFromInvalidServiceThrowsException(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => 'sendmail',
                        'extends' => 'invalid',
                    ],
                ],
            ],
        ]);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(RendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Provided service "invalid" to extend from is not configured inside acmailer_options.mail_services',
        );
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /** @test */
    public function extendedConfigIsProperlyApplied(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'extends' => 'another',
                    ],
                    'another' => [
                        'extends' => 'foo',
                        'renderer' => 'my_renderer',
                    ],
                    'foo' => [
                        'transport' => 'my_transport',
                    ],
                ],
            ],
        ]);
        $this->container->has('my_transport')->willReturn(true)->shouldBeCalled();
        $this->container->get('my_transport')->willReturn(
            $this->prophesize(TransportInterface::class)->reveal(),
        )->shouldBeCalled();
        $this->container->get('my_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        )->shouldBeCalled();
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);
    }

    /** @test */
    public function listenersAreAttached(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => 'sendmail',
                        'mail_listeners' => [
                            $this->prophesize(MailListenerInterface::class)->reveal(),
                            'my_lazy_listener',
                            [
                                'listener' => 'another_lazy_listener',
                            ],
                            [
                                'listener' => 'yet_another_lazy_listener',
                                'priority' => 3,
                            ],
                            [
                                'listener' => $this->prophesize(MailListenerInterface::class)->reveal(),
                                'priority' => 4,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $mailService = ($this->factory)($this->container->reveal(), 'acmailer.mailservice.default');

        $dispatcher = $this->getObjectProp($mailService, 'dispatcher');
        $listenersQueue = $this->getObjectProp($dispatcher, 'listenersQueue');

        $this->assertCount(3, $listenersQueue);
        $this->assertCount(3, $listenersQueue[1]); // Three listeners have default priority, which is 1
        $this->assertCount(1, $listenersQueue[3]); // One listener has priority 3
        $this->assertCount(1, $listenersQueue[4]); // One listener has priority 4
    }

    /**
     * @test
     * @dataProvider provideRenderers
     */
    public function properRendererIsUsedDependingOnTheConfiguration(
        string $rendererClass,
        string $expectedRenderer
    ): void {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'renderer' => $rendererClass,
                    ],
                ],
            ],
        ]);
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $getRenderer = $this->container->get($rendererClass)->willReturn($this->prophesize($rendererClass)->reveal());

        $mailService = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
        $renderer = $this->getObjectProp($mailService, 'renderer');

        $this->assertInstanceOf($expectedRenderer, $renderer);
        $getRenderer->shouldHaveBeenCalled();
    }

    public function provideRenderers(): iterable
    {
        yield [MailViewRendererInterface::class, MailViewRendererInterface::class];
        yield [TemplateRendererInterface::class, MezzioMailViewRenderer::class];
        yield [RendererInterface::class, MvcMailViewRenderer::class];
    }

    /** @test */
    public function configurationIsOverwrittenByProvidedOptions(): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => [
                        'transport' => 'sendmail',
                        'renderer' => 'foo_renderer',
                    ],
                ],
            ],
        ]);
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $getFooRenderer = $this->container->get('foo_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $getBarRenderer = $this->container->get('bar_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );

        $mailService = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default', [
            'transport' => 'in_memory',
            'renderer' => 'bar_renderer',
        ]);
        $transport = $this->getObjectProp($mailService, 'transport');

        $this->assertInstanceOf(InMemory::class, $transport);
        $getFooRenderer->shouldNotHaveBeenCalled();
        $getBarRenderer->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider provideThrowOnCancelConfig
     */
    public function expectedThrowOnCancelValueIsSetBasedOnConfig(array $config, bool $expected): void
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => $config,
                ],
            ],
        ]);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal(),
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal(),
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal(),
        );

        $mailService = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
        $throwOnCancel = $this->getObjectProp($mailService, 'throwOnCancel');

        $this->assertEquals($expected, $throwOnCancel);
    }

    public function provideThrowOnCancelConfig(): iterable
    {
        yield [['transport' => 'sendmail'], false];
        yield [['transport' => 'sendmail', 'throw_on_cancel' => false], false];
        yield [['transport' => 'sendmail', 'throw_on_cancel' => true], true];
    }

    /**
     * @return mixed
     */
    private function getObjectProp(object $obj, string $propName)
    {
        $ref = new ReflectionObject($obj);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);
        return $prop->getValue($obj);
    }
}
