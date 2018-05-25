<?php
declare(strict_types=1);

namespace AcMailer\View;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

class MvcMailViewRenderer implements MailViewRendererInterface
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(string $name, array $params = []): string
    {

        $layout = $params['layout'] ?? null;
        unset($params['layout']);

        $viewModel = new ViewModel($params);
        $viewModel->setTemplate($name);

        // If a layout was provided, add the original view model as a child
        if ($layout !== null) {
				            
            $childTemplateName = $params['child_template_name'] ?? 'content';
            $layoutModelParams = [
                $childTemplateName => $this->renderer->render($viewModel)
            ];
				            
            if ( isset($params['layout_params']) ) {
                if ( is_bool($params['layout_params'] && $params['layout_params'] === true) ) {
                   $layoutModelParams = array_merge($layoutModelParams, $params);
                }
                if ( is_array($params['layout_params']) ) {
                    $layoutModelParams = array_merge($layoutModelParams, $params['layout_params']);
	        }
	    }
	
	    $layoutModel = new ViewModel($layoutModelParams);
	    $layoutModel->setTemplate($layout);
            $viewModel = $layoutModel;
	}

        return $this->renderer->render($viewModel);
    }
}
