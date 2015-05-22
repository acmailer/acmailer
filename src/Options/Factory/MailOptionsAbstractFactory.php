<?php
namespace AcMailer\Options\Factory;

use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Options\MailOptions;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ErrorHandler;

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

        // Try to extend from another configuration if defined and exists
        if (isset($specificConfig['extends']) && is_string($specificConfig['extends'])) {
            $extendsConfigKey = trim($specificConfig['extends']);
            if (array_key_exists($extendsConfigKey, $config) && is_array($config[$extendsConfigKey])) {
                $specificConfig = ArrayUtils::merge($config[$extendsConfigKey], $specificConfig);
            }

            unset($specificConfig['extends']);
        }
        
        return new MailOptions($specificConfig);
    }
}
