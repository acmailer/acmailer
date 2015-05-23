<?php
namespace AcMailer\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractAcMailerFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
abstract class AbstractAcMailerFactory implements AbstractFactoryInterface
{
    const ACMAILER_PART = 'acmailer';
    const SPECIFIC_PART = '';

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
        $parts = explode('.', $name);
        if (count($parts) !== 3) {
            return false;
        }

        if ($parts[0] !== self::ACMAILER_PART || $parts[1] !== static::SPECIFIC_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config = $this->getConfig($serviceLocator);
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        if (isset($config['acmailer_options']) && is_array($config['acmailer_options'])) {
            return $config['acmailer_options'];
        } elseif (isset($config['mail_options']) && is_array($config['mail_options'])) {
            return $config['mail_options'];
        }

        return [];
    }
}
