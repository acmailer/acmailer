<?php
namespace AcMailer;

/**
 * Module class
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
