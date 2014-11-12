<?php
namespace AcMailer\Service\Factory;

use Zend\Debug\Debug;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use AcMailer\Service\MailService;
use AcMailer\Options\MailOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Constructs a new MailService injecting on it a Message and Transport object constructed with mail options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        /* @var MailOptions $mailOptions */
        $mailOptions = $sm->get('AcMailer\Options\MailOptions');

        // Prepare Mail Message
        $message = new Message();
        $message->setFrom($mailOptions->getFrom(), $mailOptions->getFromName())
                ->setTo($mailOptions->getTo())
                ->setCc($mailOptions->getCc())
                ->setBcc($mailOptions->getBcc());

        // Prepare Mail Transport
        $serviceName = $mailOptions->getMailAdapterService();
        $transport = isset($serviceName) ? $sm->get($serviceName) : $mailOptions->getMailAdapter();
        $this->setupSpecificConfig($transport, $mailOptions);

        // Prepare MailService
        $renderer       = $this->createRenderer($sm);
        $mailService    = new MailService($message, $transport, $renderer);
        $mailService->setSubject($mailOptions->getSubject());

        // Set body, either by using a template or the body option
        $template = $mailOptions->getTemplate();
        if ($template->getUseTemplate() === true) {
            $mailService->setTemplate($template->toViewModel());
        } else {
            $mailService->setBody($mailOptions->getBody());
        }

        // Attach files
        $files = $mailOptions->getAttachments()->getFiles();
        $mailService->addAttachments($files);
        // Attach files from dir
        $dir = $mailOptions->getAttachments()->getDir();
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
     * Configures specific transport options
     * @param TransportInterface $transport
     * @param MailOptions $mailOptions
     */
    protected function setupSpecificConfig(TransportInterface $transport, MailOptions $mailOptions)
    {
        if ($transport instanceof Smtp) {
            $connConfig = array(
                'username' => $mailOptions->getSmtpUser(),
                'password' => $mailOptions->getSmtpPassword(),
            );

            // Check if SSL should be used
            if ($mailOptions->getSsl() !== false) {
                $connConfig['ssl'] = $mailOptions->getSsl();
            }

            // Set SMTP transport options
            $transport->setOptions(new SmtpOptions(array(
                'host'              => $mailOptions->getServer(),
                'port'              => $mailOptions->getPort(),
                'connection_class'  => $mailOptions->getConnectionClass(),
                'connection_config' => $connConfig,
            )));
        } elseif ($transport instanceof File) {
            $transport->setOptions(new FileOptions(array(
                'path'      => $mailOptions->getFilePath(),
                'callback'  => $mailOptions->getFileCallback()
            )));
        }
    }

    /**
     * @param ServiceLocatorInterface $sm
     * @return RendererInterface
     */
    protected function createRenderer(ServiceLocatorInterface $sm)
    {
        // Try to return the configured renderer. If it points to an undefined service, create a renderer on the fly
        try {
            return $sm->get('mailviewrenderer');
        } catch (ServiceNotFoundException $e) {
            // In case the renderer service is not defined, try to construct it
            $vmConfig = $this->getViewManagerConfig($sm);
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
        $config = new Config($this->getViewHelpersConfig($sm));
        $config->configureServiceManager($helperManager);
        return $helperManager;
    }

    /**
     * Returns the view manager configuration
     * @param ServiceLocatorInterface $sm
     * @return array
     */
    protected function getViewManagerConfig(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config');
        return !empty($config) && isset($config['view_manager']) ? $config['view_manager'] : array();
    }

    /**
     * Returns the view helpers configuration
     * @param ServiceLocatorInterface $sm
     * @return array
     */
    protected function getViewHelpersConfig(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config');
        return !empty($config) && isset($config['view_helpers']) ? $config['view_helpers'] : array();
    }
}
