<?php
namespace AcMailer\Controller\Factory;

use AcMailer\Controller\ConfigMigrationController;
use AcMailer\Service\ConfigMigrationServiceInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConfigMigrationControllerFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ControllerManager $serviceLocator */
        /** @var array $config */
        $config = $serviceLocator->getServiceLocator()->get('config');
        /** @var ConfigMigrationServiceInterface $configMigrationService */
        $configMigrationService = $serviceLocator->getServiceLocator()->get('AcMailer\Service\ConfigMigrationService');

        return new ConfigMigrationController($configMigrationService, $config);
    }
}
