<?php
declare(strict_types=1);

namespace AcMailerTest\Service\Factory;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Model\EmailBuilder;
use AcMailer\Model\EmailBuilderInterface;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailService;
use AcMailer\View\ExpressiveMailViewRenderer;
use AcMailer\View\MailViewRendererInterface;
use AcMailer\View\MvcMailViewRenderer;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionObject;
use stdClass;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\TransportInterface;
use Zend\View\Renderer\RendererInterface;
use function implode;
use function sprintf;

class MailServiceAbstractFactoryTest extends TestCase
{
    /** @var MailServiceAbstractFactory */
    private $factory;
    /** @var ObjectProphecy */
    private $container;

    public function setUp()
    {
        $this->factory = new MailServiceAbstractFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideServiceNames
     * @param string $requestedName
     * @param bool $expected
     */
    public function canCreateReturnsProperResult(string $requestedName, bool $expected)
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

    public function provideServiceNames(): array
    {
        return [
            ['invalid', false],
            ['acmailer.mailservice', false],
            ['foo.mailservice.invalid', false],
            ['acmailer.foo.bar', false],
            ['foo.bar.baz', false],
            ['acmailer.mailservice.invalid', false],
            ['acmailer.mailservice.default', true],
        ];
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfRequestedConfigIsNotFound()
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
     * @param array $config
     */
    public function serviceIsCreatedIfConfigIsCorrect(array $config)
    {
        $this->container->get('config')->willReturn([
            'acmailer_options' => [
                'mail_services' => [
                    'default' => $config,
                ],
            ],
        ]);
        $this->container->has(Sendmail::class)->willReturn(false);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal()
        );
        $this->container->get('my_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal()
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal()
        );

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);
    }

    public function provideValidServiceConfig()
    {
        return [
            [[
                'transport' => 'sendmail',
            ]],
            [[
                'transport' => new Smtp(),
            ]],
            [[
                'transport' => new Smtp(),
                'renderer' => 'my_renderer',
            ]],
        ];
    }

    /**
     * @test
     */
    public function standardTransportAsServiceIsKeptAsIs()
    {
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal()
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal()
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
     * @param $transport
     * @param bool $inContainer
     */
    public function exceptionIsThrownIfConfiguredTransportHasAnInvalidValue($transport, bool $inContainer)
    {
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
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    public function provideInvalidTransports(): array
    {
        return [
            [new stdClass(), false],
            [800, false],
            ['my_transport', true],
            ['my_transport', false],
        ];
    }

    /**
     * @test
     */
    public function wrongCustomRendererThrowsException()
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
        $this->container->has(Sendmail::class)->willReturn(false);
        $this->container->get('foo_renderer')->willReturn(new stdClass());

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Defined renderer of type "%s" is not valid. The renderer must resolve to a instance of ["%s"] types',
            stdClass::class,
            implode(
                '", "',
                [MailViewRendererInterface::class, TemplateRendererInterface::class, RendererInterface::class]
            )
        ));
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /**
     * @test
     */
    public function recursiveLoopOnExtendsThrowsException()
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
        $this->container->has(Sendmail::class)->willReturn(false);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(RendererInterface::class)->reveal()
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );

        $this->expectException(Exception\ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            'It wasn\'t possible to create a mail service due to circular inheritance. Review "extends".'
        );
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /**
     * @test
     */
    public function extendFromInvalidServiceThrowsException()
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
        $this->container->has(Sendmail::class)->willReturn(false);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(RendererInterface::class)->reveal()
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Provided service "invalid" to extend from is not configured inside acmailer_options.mail_services'
        );
        $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');
    }

    /**
     * @test
     */
    public function extendedConfigIsProperlyApplied()
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
            $this->prophesize(TransportInterface::class)->reveal()
        )->shouldBeCalled();
        $this->container->get('my_renderer')->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal()
        )->shouldBeCalled();
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal()
        );

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);
    }

    /**
     * @test
     */
    public function listenersAreAttached()
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
                                'priority' => 3,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->container->has(Sendmail::class)->willReturn(false);
        $this->container->get(MailViewRendererInterface::class)->willReturn(
            $this->prophesize(MailViewRendererInterface::class)->reveal()
        );
        $this->container->get(EmailBuilder::class)->willReturn(
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal()
        );

        $result = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $this->assertInstanceOf(MailService::class, $result);

        $ref = new ReflectionObject($result->getEventManager());
        $prop = $ref->getProperty('events');
        $prop->setAccessible(true);
        $listeners = $prop->getValue($result->getEventManager());

        $this->assertCount(4, $listeners);
        foreach (MailEvent::getEventNames() as $method => $eventName) {
            $this->assertArrayHasKey($eventName, $listeners);
        }
    }

    /**
     * @test
     * @dataProvider provideRenderers
     */
    public function properRendererIsUsedDependingOnTheConfiguration(string $rendererClass, string $expectedRenderer)
    {
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
            $this->prophesize(EmailBuilderInterface::class)->reveal()
        );
        $this->container->get(AttachmentParserManager::class)->willReturn(
            $this->prophesize(AttachmentParserManager::class)->reveal()
        );

        $getRenderer = $this->container->get($rendererClass)->willReturn($this->prophesize($rendererClass)->reveal());

        $mailService = $this->factory->__invoke($this->container->reveal(), 'acmailer.mailservice.default');

        $ref = new ReflectionObject($mailService);
        $prop = $ref->getProperty('renderer');
        $prop->setAccessible(true);
        $renderer = $prop->getValue($mailService);

        $this->assertInstanceOf($expectedRenderer, $renderer);
        $getRenderer->shouldHaveBeenCalled();
    }

    public function provideRenderers(): array
    {
        return [
            [MailViewRendererInterface::class, MailViewRendererInterface::class],
            [TemplateRendererInterface::class, ExpressiveMailViewRenderer::class],
            [RendererInterface::class, MvcMailViewRenderer::class],
        ];
    }
}
