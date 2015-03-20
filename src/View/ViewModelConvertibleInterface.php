<?php
namespace AcMailer\View;

use Zend\View\Model\ViewModel;

/**
 * Interface ViewModelConvertibleInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface ViewModelConvertibleInterface
{
    /**
     * @return ViewModel
     */
    public function toViewModel();
}
