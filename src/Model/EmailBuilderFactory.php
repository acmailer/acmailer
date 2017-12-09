<?php
declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EmailBuilderFactory
{
    /**
     * @param ContainerInterface $container
     * @return EmailBuilder
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException('Cannot find a config array in the container');
        }
        $config = $container->get('config');

        return new EmailBuilder($config['acmailer_options']['emails'] ?? []);
    }
}
