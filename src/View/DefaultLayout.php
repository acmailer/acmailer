<?php
namespace AcMailer\View;

use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

/**
 * Class DefaultLayout
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class DefaultLayout implements DefaultLayoutInterface
{
    /**
     * @var ViewModel
     */
    protected $model;
    /**
     * @var string
     */
    protected $templateCaptureTo;

    /**
     * @param string|ViewModel|null $layout
     * @param array $params
     * @param string $templateCaptureTo
     */
    public function __construct($layout = null, array $params = [], $templateCaptureTo = 'content')
    {
        $this->processLayout($layout, $params);
        $this->templateCaptureTo = $templateCaptureTo;
    }

    private function processLayout($layout, array $params)
    {
        if ($layout instanceof ViewModel) {
            // Set the model as is when a ViewModel has been set
            $currentVariables = $layout->getVariables();
            if ($currentVariables instanceof \Traversable) {
                $currentVariables = ArrayUtils::iteratorToArray($currentVariables);
            }

            $layout->setVariables(array_merge($currentVariables, $params));
            $this->model = $layout;
        } elseif (is_string($layout)) {
            // Create a new ViewModel when a string is provided
            $this->model = new ViewModel($params);
            $this->model->setTemplate($layout);
        } else {
            // Unset the model in any other case
            $this->model = null;
        }
    }

    /**
     * @return ViewModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return boolean
     */
    public function hasModel()
    {
        return isset($this->model);
    }

    /**
     * @return string
     */
    public function getTemplateCaptureTo()
    {
        return $this->templateCaptureTo;
    }
}
