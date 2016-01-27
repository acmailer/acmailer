<?php
namespace AcMailer\Options\Factory;

use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Options\MailOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Class MailOptionsAbstractFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsAbstractFactory extends AbstractAcMailerFactory
{
    const SPECIFIC_PART = 'mailoptions';

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
        $config = $this->getConfig($serviceLocator);
        $specificConfig = $config[$specificServiceName];
        if (! is_array($specificConfig)) {
            $specificConfig = [];
        }

        do {
            // Get extends
            $extendsConfigKey = isset($specificConfig['extends']) && is_string($specificConfig['extends'])
                ? trim($specificConfig['extends'])
                : null;

            // Always unset the extends, in case it had a value null, to prevent the MailOptions object to throw an
            // exception
            unset($specificConfig['extends']);

            // Try to extend from another configuration if defined and exists
            if (! is_null($extendsConfigKey)
                && array_key_exists($extendsConfigKey, $config)
                && is_array($config[$extendsConfigKey])
            ) {
                $specificConfig = ArrayUtils::merge($config[$extendsConfigKey], $specificConfig);
            }
        } while ($extendsConfigKey != null);

        return new MailOptions($specificConfig);
    }
}
