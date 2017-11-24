<?php
namespace AcMailer;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Module class
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
