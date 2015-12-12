<?php
namespace AcMailer;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

/**
 * Module class
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class Module implements ConfigProviderInterface, ConsoleUsageProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'acmailer parse-config [--configKey=] [--format=(php|xml|ini|json)] [--outputFile=]'
                => 'Parses the configuration of AcMailer module <=v4.5.1 to the structure used in v5.0.0'
        ];
    }
}
