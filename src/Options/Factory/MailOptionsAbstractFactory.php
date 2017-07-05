<?php
namespace AcMailer\Options\Factory;

use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Options\MailOptions;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
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
        $specificServiceName = \explode('.', $requestedName)[2];
        $config = $this->getConfig($container);
        $specificConfig = $config[$specificServiceName];
        if (! \is_array($specificConfig)) {
            $specificConfig = [];
        }

        do {
            // Get extends
            $extendsConfigKey = isset($specificConfig['extends']) && \is_string($specificConfig['extends'])
                ? \trim($specificConfig['extends'])
                : null;

            // Always unset the extends, in case it had a value null, to prevent the MailOptions object to throw an
            // exception
            unset($specificConfig['extends']);

            // Try to extend from another configuration if defined and exists
            if (! \is_null($extendsConfigKey)
                && \array_key_exists($extendsConfigKey, $config)
                && \is_array($config[$extendsConfigKey])
            ) {
                $specificConfig = ArrayUtils::merge($config[$extendsConfigKey], $specificConfig);
            }
        } while ($extendsConfigKey != null);

        return new MailOptions($specificConfig);
    }
}
