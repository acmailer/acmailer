<?php
namespace AcMailer\Options\Factory;

use AcMailer\Options\MailOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MailOptionsFactory
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $this->getConfig($serviceLocator);
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
