<?php
declare(strict_types=1);

namespace AcMailer\Service\Factory;

use AcMailer\Attachment\AttachmentParserManager;
use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Model\EmailBuilder;
use AcMailer\Service\MailService;
use AcMailer\View\MailViewRendererFactory;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\Exception\InvalidArgumentException;
use Zend\EventManager\LazyListenerAggregate;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Transport;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\Stdlib\ArrayUtils;

class MailServiceAbstractFactory implements AbstractFactoryInterface
{
    const ACMAILER_PART = 'acmailer';
    const MAIL_SERVICE_PART = 'mailservice';
    const TRANSPORT_MAP = [
        'sendmail' => Transport\Sendmail::class,
        'smtp' => Transport\Smtp::class,
        'file' => Transport\File::class,
        'in_memory' => Transport\InMemory::class,
        'null' => Transport\InMemory::class,
    ];

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $parts = \explode('.', $requestedName);
        if (\count($parts) !== 3) {
            return false;
        }

        if ($parts[0] !== self::ACMAILER_PART || $parts[1] !== static::MAIL_SERVICE_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config = $container->get('config')['acmailer_options']['mail_services'] ?? [];
        return \array_key_exists($specificServiceName, $config);
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MailService
     * @throws InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MailService
    {
        $specificServiceName = \explode('.', $requestedName)[2] ?? null;
        $mailOptions = $container->get('config')['acmailer_options'] ?? [];
        $specificMailServiceOptions = $mailOptions['mail_services'][$specificServiceName] ?? null;

        if ($specificMailServiceOptions === null) {
            throw new Exception\ServiceNotCreatedException(\sprintf(
                'Requested MailService with name "%s" could not be found. Make sure you have registered it with name'
                . ' "%s" under the acmailer_options.mail_services config entry',
                $requestedName,
                $specificServiceName
            ));
        }

        // Recursively extend configuration
        $specificMailServiceOptions = $this->buildConfig($mailOptions, $specificMailServiceOptions);

        // Create the service
        $transport = $this->createTransport($container, $specificMailServiceOptions);
        $renderer = $this->createRenderer($container, $specificMailServiceOptions);
        $mailService = new MailService(
            $transport,
            $renderer,
            $container->get(EmailBuilder::class),
            $container->get(AttachmentParserManager::class)
        );

        // Attach mail listeners
        $this->attachMailListeners($mailService, $container, $specificMailServiceOptions);
        return $mailService;
    }

    private function buildConfig(array $mailOptions, array $specificOptions): array
    {
        if (! isset($specificOptions['extends'])) {
            return $specificOptions;
        }

        // Recursively extend
        $mailServices = $mailOptions['mail_services'];
        $processedExtends = [];
        do {
            $serviceToExtend = $specificOptions['extends'] ?? null;
            // Unset the extends value to allow recursive inheritance
            unset($specificOptions['extends']);

            // Prevent an infinite loop by self inheritance
            if (\in_array($serviceToExtend, $processedExtends, true)) {
                throw new Exception\ServiceNotCreatedException(
                    'It wasn\'t possible to create a mail service due to circular inheritance. Review "extends".'
                );
            }
            $processedExtends[] = $serviceToExtend;

            // Ensure the service from which we have to extend has been configured
            if (! isset($mailServices[$serviceToExtend])) {
                throw new Exception\InvalidArgumentException(\sprintf(
                    'Provided service "%s" to extend from is not configured inside acmailer_options.mail_services',
                    $serviceToExtend
                ));
            }

            $specificOptions = ArrayUtils::merge($mailServices[$serviceToExtend], $specificOptions);
        } while (isset($specificOptions['extends']));

        return $specificOptions;
    }

    /**
     * @param ContainerInterface $container
     * @param array $mailOptions
     * @return Transport\TransportInterface
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createTransport(ContainerInterface $container, array $mailOptions): Transport\TransportInterface
    {
        $transport = $mailOptions['transport'] ?? Transport\Sendmail::class;
        if (! \is_string($transport) && ! $transport instanceof Transport\TransportInterface) {
            // The adapter is not valid. Throw an exception
            throw Exception\InvalidArgumentException::fromValidTypes(
                ['string', Transport\TransportInterface::class],
                $transport
            );
        }

        // A transport instance can be returned as is
        if ($transport instanceof Transport\TransportInterface) {
            return $this->setupTransportConfig($transport, $mailOptions);
        }

        // Check if the adapter is one of Zend's default adapters
        $transport = self::TRANSPORT_MAP[$transport] ?? $transport;
        if (\is_subclass_of($transport, Transport\TransportInterface::class)) {
            return $this->setupTransportConfig(new $transport(), $mailOptions);
        }

        // Check if the transport is a service
        if ($container->has($transport)) {
            /** @var Transport\TransportInterface $transport */
            $transportInstance = $container->get($transport);
            if ($transportInstance instanceof Transport\TransportInterface) {
                return $this->setupTransportConfig($transportInstance, $mailOptions);
            }

            throw new Exception\InvalidArgumentException(\sprintf(
                'Provided transport service with name "%s" does not return a "%s" instance',
                $transport,
                Transport\TransportInterface::class
            ));
        }

        // The adapter is not valid. Throw an exception
        throw new Exception\InvalidArgumentException(\sprintf(
            'Registered transport "%s" is not either one of ["%s"], a "%s" subclass or a registered service.',
            $transport,
            \implode('", "', \array_keys(self::TRANSPORT_MAP)),
            Transport\TransportInterface::class
        ));
    }

    /**
     * @param Transport\TransportInterface $transport
     * @param array $mailOptions
     * @return Transport\TransportInterface
     */
    private function setupTransportConfig(
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
     * @param ContainerInterface $container
     * @param array $mailOptions
     * @return TemplateRendererInterface
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createRenderer(ContainerInterface $container, array $mailOptions): TemplateRendererInterface
    {
        if (! isset($mailOptions['renderer'])) {
            return $container->get(MailViewRendererFactory::SERVICE_NAME);
        }

        // Resolve renderer service and ensure it has proper type
        $renderer = $container->get($mailOptions['renderer']);
        if (! $renderer instanceof TemplateRendererInterface) {
            throw new Exception\InvalidArgumentException(\sprintf(
                'Defined renderer of type "%s" is not valid. The renderer must resolve to a "%s" instance',
                \is_object($renderer) ? \get_class($renderer) : \gettype($renderer),
                TemplateRendererInterface::class
            ));
        }
        return $renderer;
    }

    /**
     * Attaches the preconfigured mail listeners to the mail service
     *
     * @param EventsCapableInterface $service
     * @param ContainerInterface $container
     * @param array $mailOptions
     * @throws InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private function attachMailListeners(
        EventsCapableInterface $service,
        ContainerInterface $container,
        array $mailOptions
    ) {
        $listeners = (array) ($mailOptions['mail_listeners'] ?? []);
        if (empty($listeners)) {
            return;
        }

        $definitions = [];
        foreach ($listeners as $listener) {
            $priority = 1;
            if (\is_array($listener) && \array_key_exists('listener', $listener)) {
                $listener = $listener['listener'];
                $priority = $listener['priority'] ?? 1;
            }

            // If the listener is already an instance, just register it
            if ($listener instanceof MailListenerInterface) {
                $listener->attach($service->getEventManager(), $priority);
                continue;
            }

            // Ensure the listener is a string
            if (! \is_string($listener)) {
                throw Exception\InvalidArgumentException::fromValidTypes(
                    ['string', MailListenerInterface::class],
                    $listener
                );
            }

            $definitions[] = [
                'listener' => $listener,
                'method' => 'onPreRender',
                'event' => MailEvent::EVENT_MAIL_PRE_RENDER,
                'priority' => $priority,
            ];
            $definitions[] = [
                'listener' => $listener,
                'method' => 'onPreSend',
                'event' => MailEvent::EVENT_MAIL_PRE_SEND,
                'priority' => $priority,
            ];
            $definitions[] = [
                'listener' => $listener,
                'method' => 'onPostSend',
                'event' => MailEvent::EVENT_MAIL_POST_SEND,
                'priority' => $priority,
            ];
            $definitions[] = [
                'listener' => $listener,
                'method' => 'onSendError',
                'event' => MailEvent::EVENT_MAIL_SEND_ERROR,
                'priority' => $priority,
            ];
        }

        // Attach lazy event listeners if any
        if (! empty($definitions)) {
            (new LazyListenerAggregate($definitions, $container))->attach($service->getEventManager());
        }
    }
}
