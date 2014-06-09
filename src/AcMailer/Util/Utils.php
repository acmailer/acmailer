<?php
namespace AcMailer\Util;

use AcMailer\Options\TemplateOptions;
use AcMailer\Exception\InvalidArgumentException;
use Zend\View\Model\ViewModel;

/**
 * Class Utils
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class Utils
{

    /**
     * Casts a template options to a ViewModel
     * @param array|\AcMailer\Options\TemplateOptions $options
     * @return ViewModel
     * @throws \AcMailer\Exception\InvalidArgumentException In case provided argument is not valid
     */
    public static function optionsToViewModel($options)
    {
        if (is_array($options)) {
            $options = new TemplateOptions($options);
        }
        if (!($options instanceof TemplateOptions)) {
            throw new InvalidArgumentException(sprintf(
                'Options argument should be an array or an instance of AcMailer\Options\TemplateOptions. %s provided',
                is_object($options) ? get_class($options) : gettype($options)
            ));
        }

        // Create the base ViewModel
        $model = new ViewModel($options->getParams());
        $model->setTemplate($options->getPath());

        // Add childs recursively
        /* @var TemplateOptions $child */
        foreach ($options->getChilds() as $captureTo => $child) {
            $model->addChild(static::optionsToViewModel($child), $captureTo);
        }

        return $model;
    }

} 