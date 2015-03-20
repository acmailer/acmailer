<?php
namespace AcMailer\Service\Factory;

use Zend\Mail\Transport\File;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use AcMailer\Service\MailService;
use AcMailer\Options\MailOptions;
use AcMailer\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Creates a new MailService instance
 *
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceFactory implements FactoryInterface
{
    /**
     * @var MailOptions
     */
    protected $mailOptions;

    public function createService(ServiceLocatorInterface $sm)
    {
        $this->mailOptions = $sm->get('AcMailer\Options\MailOptions');

        // Create the service
        $message        = $this->createMessage();
        $transport      = $this->createTransport($sm);
        $renderer       = $this->createRenderer($sm);
        $mailService    = new MailService($message, $transport, $renderer);

        // Set subject
        $mailService->setSubject($this->mailOptions->getMessageOptions()->getSubject());

        // Set body, either by using a template or a raw body
        $body = $this->mailOptions->getMessageOptions()->getBody();
        if ($body->getUseTemplate()) {
            $mailService->setTemplate($body->getTemplate()->toViewModel());
        } else {
            $mailService->setBody($body->getContent(), $body->getCharset());
        }

        // Attach files
        $files = $this->mailOptions->getMessageOptions()->getAttachments()->getFiles();
        $mailService->addAttachments($files);

        // Attach files from dir
        $dir = $this->mailOptions->getMessageOptions()->getAttachments()->getDir();
        if ($dir['iterate'] === true && is_string($dir['path']) && is_dir($dir['path'])) {
            $files = $dir['recursive'] === true ?
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir['path'], \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ):
                new \DirectoryIterator($dir['path']);

            /* @var \SplFileInfo $fileInfo */
            foreach ($files as $fileInfo) {
                if ($fileInfo->isDir()) {
                    continue;
                }
                $mailService->addAttachment($fileInfo->getPathname());
            }
        }

        return $mailService;
    }

    /**
     * @return Message
     */
    protected function createMessage()
    {
        $options = $this->mailOptions->getMessageOptions();
        $message = new Message();
        $message->setFrom($options->getFrom(), $options->getFromName())
            ->setTo($options->getTo())
            ->setCc($options->getCc())
            ->setBcc($options->getBcc());

        return $message;
    }

    /**
     * @param ServiceLocatorInterface $sm
     * @return TransportInterface
     */
    protected function createTransport(ServiceLocatorInterface $sm)
    {
        $adapter = $this->mailOptions->getMailAdapter();
        // A transport instance can be returned as is
        if ($adapter instanceof TransportInterface) {
            return $this->setupTransportConfig($adapter);
        }

        // Check if the adapter is a service
        if (is_string($adapter) && $sm->has($adapter)) {
            /** @var TransportInterface $transport */
            $transport = $sm->get($adapter);
            if ($transport instanceof TransportInterface) {
                return $this->setupTransportConfig($transport);
            } else {
                throw new InvalidArgumentException(
                    'Provided mail_adapter service does not return a "Zend\Mail\Transport\TransportInterface" instance'
                );
            }
        }

        // Check if the adapter is one of Zend's default adapters
        if (is_string($adapter) && is_subclass_of($adapter, 'Zend\Mail\Transport\TransportInterface')) {
            return $this->setupTransportConfig(new $adapter());
        }

        // The adapter is not valid. Throw an exception
        throw new InvalidArgumentException(sprintf(
            'mail_adapter must be an instance of "Zend\Mail\Transport\TransportInterface" or string, "%s" provided',
            is_object($adapter) ? get_class($adapter) : gettype($adapter)
        ));
    }

    /**
     * @param TransportInterface $transport
     * @return TransportInterface
     */
    protected function setupTransportConfig(TransportInterface $transport)
    {
        if ($transport instanceof Smtp) {
            $transport->setOptions($this->mailOptions->getSmtpOptions());
        } elseif ($transport instanceof File) {
            $transport->setOptions($this->mailOptions->getFileOptions());
        }

        return $transport;
    }

    /**
     * @param ServiceLocatorInterface $sm
     * @return RendererInterface
     */
    protected function createRenderer(ServiceLocatorInterface $sm)
    {
        // Try to return the configured renderer. If it points to an undefined service, create a renderer on the fly
        try {
            $renderer = $sm->get('mailviewrenderer');
            return $renderer;
        } catch (ServiceNotFoundException $e) {
            // In case the renderer service is not defined, try to construct it
            $vmConfig = $this->getSpecificConfig($sm, 'view_manager');
            $renderer = new PhpRenderer();

            // Check what kind of view_manager configuration has been defined
            if (isset($vmConfig['template_map']) && isset($vmConfig['template_path_stack'])) {
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
            $renderer->setHelperPluginManager($this->createHelperPluginManager($sm));
            return $renderer;
        }
    }

    /**
     * Creates a view helper manager
     * @param ServiceLocatorInterface $sm
     * @return HelperPluginManager
     */
    protected function createHelperPluginManager(ServiceLocatorInterface $sm)
    {
        $factory = new ViewHelperManagerFactory();
        /** @var HelperPluginManager $helperManager */
        $helperManager = $factory->createService($sm);
        $config = new Config($this->getSpecificConfig($sm, 'view_helpers'));
        $config->configureServiceManager($helperManager);
        return $helperManager;
    }

    /**
     * Returns a specific configuration defined by provided key
     * @param ServiceLocatorInterface $sm
     * @param $configKey
     * @return array
     */
    protected function getSpecificConfig(ServiceLocatorInterface $sm, $configKey)
    {
        $config = $sm->get('Config');
        return ! empty($config) && isset($config[$configKey]) ? $config[$configKey] : [];
    }
}
