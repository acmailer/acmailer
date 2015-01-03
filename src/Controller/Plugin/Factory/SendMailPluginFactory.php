<?php
namespace AcMailer\Controller\Plugin\Factory;

use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Service\MailServiceInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;

/**
 * Class SendMailPluginFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPluginFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ControllerPluginManager $serviceLocator */
        /** @var MailServiceInterface $mailService */
        $mailService = $serviceLocator->getServiceLocator()->get('AcMailer\Service\MailService');
        return new SendMailPlugin($mailService);
    }
}
