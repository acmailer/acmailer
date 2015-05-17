<?php
namespace AcMailer\Options\Factory;

use AcMailer\Options\MailOptions;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ErrorHandler;

/**
 * Class MailOptionsAbstractFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsAbstractFactory implements AbstractFactoryInterface
{
    const ACMAILER_PART = 'acmailer';
    const MAIL_OPTIONS_PART = 'mailoptions';

    /**
     * Determine if we can create a service with name
     * Services can be created if they have the structure acmailer.mailoptions.default
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

        if ($parts[0] !== self::ACMAILER_PART || $parts[1] !== self::MAIL_OPTIONS_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config = $this->getConfig($serviceLocator);
        return array_key_exists($specificServiceName, $config);
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
        $specificServiceName = explode('.', $name)[2];
        $config = $this->getConfig($serviceLocator)[$specificServiceName];
        if (! is_array($config)) {
            $config = [];
        }

        return new MailOptions($config);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    private function getConfig(ServiceLocatorInterface $serviceLocator)
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
