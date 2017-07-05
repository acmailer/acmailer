<?php
namespace AcMailer\Controller\Plugin\Factory;

use AcMailer\Controller\Plugin\SendMailPlugin;
use AcMailer\Factory\AbstractAcMailerFactory;
use AcMailer\Service\Factory\MailServiceAbstractFactory;
use AcMailer\Service\MailServiceInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Stdlib\StringUtils;

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
     * @param ContainerInterface $container
     * @param $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        /** @var ControllerPluginManager $serviceLocator */
        if (\strpos($requestedName, 'sendMail') !== 0) {
            return false;
        }

        if ($requestedName === 'sendMail') {
            return true;
        }

        $specificServiceName = $this->getSpecificServiceName($requestedName);
        return \array_key_exists($specificServiceName, $this->getConfig($container));
    }

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
        $specificServiceName = $this->getSpecificServiceName($requestedName);
        /** @var MailServiceInterface $mailService */
        $mailService = $container->get(
            \sprintf('%s.%s.%s', self::ACMAILER_PART, MailServiceAbstractFactory::SPECIFIC_PART, $specificServiceName)
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
        $parts = \explode('_', $this->camelCaseToUnderscore($requestedName));
        if (\count($parts) === 2) {
            return 'default';
        }

        // Discard the sendMail part
        $parts = \array_slice($parts, 2);
        $specificServiceName = '';
        foreach ($parts as $part) {
            $specificServiceName .= $part;
        }

        // Convert from camelcase to underscores and set to lower
        return \strtolower($specificServiceName);
    }

    protected function camelCaseToUnderscore($value)
    {
        if (!\is_scalar($value) && !\is_array($value)) {
            return $value;
        }

        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = ['_\1', '_\1'];
        } else {
            $pattern     = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1_\2', '_\1'];
        }

        return \preg_replace($pattern, $replacement, $value);
    }
}
