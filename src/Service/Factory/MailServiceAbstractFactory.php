<?php
namespace AcMailer\Service\Factory;

use AcMailer\Event\MailEvent;
use AcMailer\Event\MailListenerInterface;
use AcMailer\Exception;
use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Model\EmailBuilder;
use AcMailer\Service\MailService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\Exception\InvalidArgumentException;
use Zend\EventManager\LazyListenerAggregate;
use Zend\Mail\Transport;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\TemplatePathStack;

class MailServiceAbstractFactory extends AbstractAcMailerFactory
{
    const SPECIFIC_PART = 'mailservice';
    const TRANSPORT_MAP = [
        'sendmail' => Transport\Sendmail::class,
        'smtp' => Transport\Smtp::class,
        'file' => Transport\File::class,
        'in_memory' => Transport\InMemory::class,
        'null' => Transport\InMemory::class,
    ];

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MailService
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

        // Create the service
        $transport = $this->createTransport($container, $specificMailServiceOptions);
        $renderer = $this->createRenderer($container, $specificMailServiceOptions);
        $mailService = new MailService($transport, $renderer, $container->get(EmailBuilder::class));

        // Attach mail listeners
        $this->attachMailListeners($mailService, $container, $specificMailServiceOptions);
        return $mailService;
    }

    /**
     * @param ContainerInterface $container
     * @param array $mailOptions
     * @return Transport\TransportInterface
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createTransport(ContainerInterface $container, array $mailOptions): Transport\TransportInterface
    {
        $transport = $mailOptions['transport'] ?? null;
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

        $transport = self::TRANSPORT_MAP[$transport] ?? $transport;

        // Check if the adapter is a service
        if ($container->has($transport)) {
            /** @var Transport\TransportInterface $transport */
            $transport = $container->get($transport);
            if ($transport instanceof Transport\TransportInterface) {
                return $this->setupTransportConfig($transport, $mailOptions);
            }

            throw new Exception\InvalidArgumentException(\sprintf(
                'Provided transport service with name "%s" does not return a "%s" instance',
                $transport,
                Transport\TransportInterface::class
            ));
        }

        // Check if the adapter is one of Zend's default adapters
        if (\is_subclass_of($transport, Transport\TransportInterface::class)) {
            return $this->setupTransportConfig(new $transport(), $mailOptions);
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
    protected function setupTransportConfig(
        Transport\TransportInterface $transport,
        array $mailOptions
    ): Transport\TransportInterface {
        if ($transport instanceof Transport\Smtp) {
            $transport->setOptions(new Transport\SmtpOptions($mailOptions['transport_options']));
        } elseif ($transport instanceof Transport\File) {
            $transport->setOptions(new Transport\FileOptions($mailOptions['transport_options']));
        }

        return $transport;
    }

    /**
     * @param ContainerInterface $container
     * @param array $mailOptions
     * @return RendererInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createRenderer(ContainerInterface $container, array $mailOptions)
    {
        // Try to return the configured renderer. If it points to an undefined service, create a renderer on the fly
        $serviceName = $mailOptions['renderer'] ?? null;

        try {
            return $container->get($serviceName);
        } catch (ServiceNotFoundException $e) {
            // In case the renderer service is not defined, try to construct it
            $vmConfig = $this->getSpecificConfig($container, 'view_manager');
            $renderer = new PhpRenderer();

            // Check what kind of view_manager configuration has been defined
            if (isset($vmConfig['template_map'], $vmConfig['template_path_stack'])) {
                // If both a template_map and a template_path_stack have been defined, create an AggregateResolver
                $pathStackResolver = new TemplatePathStack();
                $pathStackResolver->setPaths($vmConfig['template_path_stack']);
                $resolver = new AggregateResolver();
                $resolver->attach($pathStackResolver)
                    ->attach(new TemplateMapResolver($vmConfig['template_map']));
                $renderer->setResolver($resolver);
            } elseif (isset($vmConfig['template_map'])) {
                // Create a TemplateMapResolver in case only the template_map has been defined
                $renderer->setResolver(new TemplateMapResolver($vmConfig['template_map']));
            } elseif (isset($vmConfig['template_path_stack'])) {
                // Create a TemplatePathStack resolver in case only the template_path_stack has been defined
                $pathStackResolver = new TemplatePathStack();
                $pathStackResolver->setPaths($vmConfig['template_path_stack']);
                $renderer->setResolver($pathStackResolver);
            }

            // Create a HelperPluginManager with default view helpers and user defined view helpers
            $renderer->setHelperPluginManager($this->createHelperPluginManager($container));
            return $renderer;
        }
    }

    /**
     * Creates a view helper manager
     * @param ContainerInterface $container
     * @return HelperPluginManager
     * @throws ContainerExceptionInterface
     */
    private function createHelperPluginManager(ContainerInterface $container): HelperPluginManager
    {
        $factory = new ViewHelperManagerFactory();
        /** @var HelperPluginManager $helperManager */
        $helperManager = $factory($container, ViewHelperManagerFactory::PLUGIN_MANAGER_CLASS);
        $config = new Config($this->getSpecificConfig($container, 'view_helpers'));
        $config->configureServiceManager($helperManager);
        return $helperManager;
    }

    /**
     * Returns a specific configuration defined by provided key
     * @param ContainerInterface $container
     * @param $configKey
     * @return array
     * @throws ContainerExceptionInterface
     */
    protected function getSpecificConfig(ContainerInterface $container, $configKey): array
    {
        return $container->get('config')[$configKey] ?? [];
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
    protected function attachMailListeners(
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
            if (\is_array($listener) && array_key_exists('listener', $listener)) {
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
