<?php

declare(strict_types=1);

namespace AcMailer\Service\Factory;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Event\EventDispatcher;
use AcMailer\Event\LazyMailListener;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Model\EmailBuilder;
use AcMailer\Service\MailService;
use AcMailer\View\MailViewRendererInterface;
use AcMailer\View\MezzioMailViewRenderer;
use AcMailer\View\MvcMailViewRenderer;
use Interop\Container\ContainerInterface;
use Laminas\Mail\Transport;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function array_keys;
use function count;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function is_subclass_of;
use function sprintf;

class MailServiceAbstractFactory implements AbstractFactoryInterface
{
    private const ACMAILER_PART = 'acmailer';
    private const MAIL_SERVICE_PART = 'mailservice';
    private const TRANSPORT_MAP = [
        'sendmail' => Transport\Sendmail::class,
        'smtp' => Transport\Smtp::class,
        'file' => Transport\File::class,
        'in_memory' => Transport\InMemory::class,
        'null' => Transport\InMemory::class,
    ];

    /**
     * Can the factory create an instance for the service?
     *
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool // phpcs:ignore
    {
        $parts = explode('.', $requestedName);
        if (count($parts) < 3) {
            return false;
        }

        [$acMailer, $mailService, $specificServiceName] = $parts;
        if ($acMailer !== self::ACMAILER_PART || $mailService !== static::MAIL_SERVICE_PART) {
            return false;
        }

        $config = $container->get('config')['acmailer_options']['mail_services'] ?? [];
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * Create an object
     *
     * @param string $requestedName
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $factoryOptions = null): MailService // phpcs:ignore
    {
        $specificServiceName = explode('.', $requestedName)[2] ?? null;
        $mailOptions = $container->get('config')['acmailer_options'] ?? [];
        $specificMailServiceOptions = $mailOptions['mail_services'][$specificServiceName] ?? null;
        $throwOnCancel = $specificMailServiceOptions['throw_on_cancel'] ?? false;

        if ($specificMailServiceOptions === null) {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'Requested MailService with name "%s" could not be found. Make sure you have registered it with name'
                . ' "%s" under the acmailer_options.mail_services config entry',
                $requestedName,
                $specificServiceName,
            ));
        }

        $specificMailServiceOptions = $this->resolveExtendedConfig($mailOptions, $specificMailServiceOptions);
        $specificMailServiceOptions = $factoryOptions === null ? $specificMailServiceOptions : ArrayUtils::merge(
            $specificMailServiceOptions,
            $factoryOptions,
        );

        $transport = $this->createTransport($container, $specificMailServiceOptions);
        $renderer = $this->createRenderer($container, $specificMailServiceOptions);
        $dispatcher = $this->createDispatcher($container, $specificMailServiceOptions);

        return new MailService(
            $transport,
            $renderer,
            $container->get(EmailBuilder::class),
            $container->get(AttachmentParserManager::class),
            $dispatcher,
            $throwOnCancel,
        );
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ServiceNotCreatedException
     */
    private function resolveExtendedConfig(array $mailOptions, array $specificOptions): array
    {
        if (! isset($specificOptions['extends'])) {
            return $specificOptions;
        }

        $mailServices = $mailOptions['mail_services'];
        $processedExtends = [];
        do {
            $serviceToExtend = $specificOptions['extends'] ?? null;
            // Unset the extends value to allow recursive inheritance
            unset($specificOptions['extends']);

            // Prevent an infinite loop by self inheritance
            if (in_array($serviceToExtend, $processedExtends, true)) {
                throw new Exception\ServiceNotCreatedException(
                    "It wasn't possible to create a mail service due to circular inheritance. Review 'extends' option.",
                );
            }
            $processedExtends[] = $serviceToExtend;

            // Ensure the service from which we have to extend has been configured
            if (! isset($mailServices[$serviceToExtend])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Provided service "%s" to extend from is not configured inside acmailer_options.mail_services',
                    $serviceToExtend,
                ));
            }

            $specificOptions = ArrayUtils::merge($mailServices[$serviceToExtend], $specificOptions);
        } while (isset($specificOptions['extends']));

        return $specificOptions;
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createTransport(ContainerInterface $container, array $mailOptions): Transport\TransportInterface
    {
        $transport = $mailOptions['transport'] ?? Transport\Sendmail::class;
        if (! is_string($transport) && ! $transport instanceof Transport\TransportInterface) {
            throw Exception\InvalidArgumentException::fromValidTypes(
                ['string', Transport\TransportInterface::class],
                $transport,
                'transport',
            );
        }

        // A transport instance can be returned as is
        if ($transport instanceof Transport\TransportInterface) {
            return $transport;
        }

        // Check if the adapter is one of Laminas' default adapters
        $transport = self::TRANSPORT_MAP[$transport] ?? $transport;
        if (is_subclass_of($transport, Transport\TransportInterface::class)) {
            return $this->setupStandardTransportFromConfig(new $transport(), $mailOptions);
        }

        // Check if the transport is a service
        if ($container->has($transport)) {
            return $container->get($transport);
        }

        // The adapter is not valid. Throw an exception
        throw new Exception\InvalidArgumentException(sprintf(
            'Registered transport "%s" is not either one of ["%s"], a "%s" subclass or a registered service.',
            $transport,
            implode('", "', array_keys(self::TRANSPORT_MAP)),
            Transport\TransportInterface::class,
        ));
    }

    private function setupStandardTransportFromConfig(
        Transport\TransportInterface $transport,
        array $mailOptions
    ): Transport\TransportInterface {
        if ($transport instanceof Transport\Smtp) {
            $transport->setOptions(new Transport\SmtpOptions($mailOptions['transport_options'] ?? []));
        } elseif ($transport instanceof Transport\File) {
            $transportOptions = $mailOptions['transport_options'] ?? [];
            $transportOptions['path'] = $transportOptions['path'] ?? 'data/mail/output';
            $transport->setOptions(new Transport\FileOptions($transportOptions));
        }

        return $transport;
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createRenderer(ContainerInterface $container, array $mailOptions): MailViewRendererInterface
    {
        if (! isset($mailOptions['renderer'])) {
            return $container->get(MailViewRendererInterface::class);
        }

        // Resolve renderer service and ensure it has proper type
        $renderer = $container->get($mailOptions['renderer']);

        if ($renderer instanceof MailViewRendererInterface) {
            return $renderer;
        }

        if ($renderer instanceof TemplateRendererInterface) {
            return new MezzioMailViewRenderer($renderer);
        }

        if ($renderer instanceof RendererInterface) {
            return new MvcMailViewRenderer($renderer);
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Defined renderer of type "%s" is not valid. The renderer must resolve to a instance of ["%s"] types',
            is_object($renderer) ? get_class($renderer) : gettype($renderer),
            implode(
                '", "',
                [MailViewRendererInterface::class, TemplateRendererInterface::class, RendererInterface::class],
            ),
        ));
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private function createDispatcher(ContainerInterface $container, array $mailOptions): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        $listeners = $mailOptions['mail_listeners'] ?? [];

        foreach ($listeners as $listener) {
            $dispatcher->attachMailListener(...$this->resolveListener($listener, $container));
        }

        return $dispatcher;
    }

    /**
     * @param array|string|MailListenerInterface $listener
     * @throws Exception\InvalidArgumentException
     */
    private function resolveListener($listener, ContainerInterface $container): array
    {
        $priority = 1;
        if (is_array($listener) && array_key_exists('listener', $listener)) {
            $priority = $listener['priority'] ?? 1;
            $listener = $listener['listener'];
        }

        if (is_string($listener)) {
            return [new LazyMailListener($listener, $container), $priority];
        }

        if ($listener instanceof MailListenerInterface) {
            return [$listener, $priority];
        }

        throw Exception\InvalidArgumentException::fromValidTypes(
            ['string', 'array', MailListenerInterface::class],
            $listener,
            'listener',
        );
    }
}
