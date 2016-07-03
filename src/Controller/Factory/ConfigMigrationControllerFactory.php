<?php
namespace AcMailer\Controller\Factory;

use AcMailer\Controller\ConfigMigrationController;
use AcMailer\Service\ConfigMigrationService;
use AcMailer\Service\ConfigMigrationServiceInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConfigMigrationControllerFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $config */
        $config = $container->get('config');
        /** @var ConfigMigrationServiceInterface $configMigrationService */
        $configMigrationService = $container->get(ConfigMigrationService::class);

        return new ConfigMigrationController($configMigrationService, $config);
    }
}
