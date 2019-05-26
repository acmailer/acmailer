<?php
declare(strict_types=1);

namespace AcMailer\Service\Factory;

use AcMailer\Service\MailServiceBuilder;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MailServiceBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $dependencies = $config['dependencies'] ?? $config['service_manager'] ?? [];

        return new MailServiceBuilder($container, $dependencies);
    }
}
