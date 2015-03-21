<?php
namespace AcMailer\View;

use Zend\View\Model\ViewModel;

/**
 * Interface DefaultLayoutInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface DefaultLayoutInterface
{
    /**
     * @return ViewModel
     */
    public function getModel();

    /**
     * @return boolean
     */
    public function hasModel();

    /**
     * @return string
     */
    public function getTemplateCaptureTo();
}
