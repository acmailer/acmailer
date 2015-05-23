<?php
namespace AcMailer\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\View\Model\ViewModel;
use AcMailer\View\ViewModelConvertibleInterface;

/**
 * Template specific options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class TemplateOptions extends AbstractOptions implements ViewModelConvertibleInterface
{
    /**
     * @var string
     */
    protected $path = 'ac-mailer/mail-templates/mail';
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @var TemplateOptions[]
     */
    protected $children = [];
    /**
     * @var array
     */
    protected $defaultLayout = [];

    /**
     * @param $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param array $children
     * @return $this
     */
    public function setChildren($children)
    {
        $children         = (array) $children;
        $this->children   = [];
        // Cast each child to a TemplateOptions object
        foreach ($children as $captureTo => $child) {
            $this->children[$captureTo] = new TemplateOptions($child);
            // Recursively add childs
            if (array_key_exists('children', $child)) {
                $this->children[$captureTo]->setChildren($child['children']);
            }
        }

        return $this;
    }
    /**
     * @return TemplateOptions[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return ViewModel
     */
    public function toViewModel()
    {
        // Create the base ViewModel
        $model = new ViewModel($this->getParams());
        $model->setTemplate($this->getPath());

        // Add childs recursively
        /* @var TemplateOptions $child */
        foreach ($this->getChildren() as $captureTo => $child) {
            $model->addChild($child->toViewModel(), $captureTo);
        }

        return $model;
    }

    /**
     * @return array
     */
    public function getDefaultLayout()
    {
        return $this->defaultLayout;
    }

    /**
     * @param array $defaultLayout
     * @return $this
     */
    public function setDefaultLayout(array $defaultLayout)
    {
        $this->defaultLayout = $defaultLayout;
        return $this;
    }
}
