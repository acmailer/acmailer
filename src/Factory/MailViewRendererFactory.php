<?php
declare(strict_types=1);

namespace AcMailer\Factory;

use Interop\Container\ContainerInterface as InteropContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\View\Exception\InvalidArgumentException;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\TemplatePathStack;

class MailViewRendererFactory
{
    const SERVICE_NAME = 'AcMailer\MailViewRenderer';

    /**
     * @param ContainerInterface $container
     * @return RendererInterface
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RendererInterface
    {
        // Try to return the configured renderer. If it points to an undefined service, create a renderer on the fly
        $serviceName = 'viewrenderer';

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
     * @param ContainerInterface|InteropContainer $container
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
    private function getSpecificConfig(ContainerInterface $container, $configKey): array
    {
        return $container->get('config')[$configKey] ?? [];
    }
}
