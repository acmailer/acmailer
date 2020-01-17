<?php

declare(strict_types=1);

namespace AcMailer\Service\Factory;

use AcMailer\Service\MailServiceBuilder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MailServiceBuilderFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MailServiceBuilder // phpcs:ignore
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $dependencies = $config['dependencies'] ?? $config['service_manager'] ?? [];

        return new MailServiceBuilder($container, $dependencies);
    }
}
