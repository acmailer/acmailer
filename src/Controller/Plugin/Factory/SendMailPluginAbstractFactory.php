<?php
namespace AcMailer\Controller\Plugin\Factory;

use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailServiceInterface;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;

/**
 * Class SendMailPluginAbstractFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPluginAbstractFactory extends AbstractAcMailerFactory
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var ControllerPluginManager $serviceLocator */
        if (strpos($requestedName, 'sendMail') !== 0) {
            return false;
        }

        if ($requestedName === 'sendMail') {
            return true;
        }

        $specificServiceName = $this->getSpecificServiceName($requestedName);
        return array_key_exists($specificServiceName, $this->getConfig($serviceLocator->getServiceLocator()));
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var ControllerPluginManager $serviceLocator */
        $specificServiceName = $this->getSpecificServiceName($requestedName);
        /** @var MailServiceInterface $mailService */
        $mailService = $serviceLocator->getServiceLocator()->get(
            sprintf('%s.%s.%s', self::ACMAILER_PART, MailServiceAbstractFactory::SPECIFIC_PART, $specificServiceName)
        );
        return new SendMailPlugin($mailService);
    }

    /**
     * Fetches a mail service name from the requested plugin name.
     * sendMailCustomers -> customers
     * sendMail -> default
     *
     * @param $requestedName
     * @return string
     */
    protected function getSpecificServiceName($requestedName)
    {
        $filter = new CamelCaseToUnderscore();
        $parts = explode('_', $filter->filter($requestedName));
        if (count($parts) === 2) {
            return 'default';
        }

        // Discard the sendMail part
        $parts = array_slice($parts, 2);
        $specificServiceName = '';
        foreach ($parts as $part) {
            $specificServiceName .= $part;
        }

        // Convert from camelcase to underscores and set to lower
        return strtolower($specificServiceName);
    }
}
