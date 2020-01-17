<?php

declare(strict_types=1);

namespace AcMailer\View;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

class MvcMailViewRenderer implements MailViewRendererInterface
{
    private RendererInterface $renderer;

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
        if ($layout) {
            $childTemplateName = $params['child_template_name'] ?? 'content';
            $params[$childTemplateName] = $this->renderer->render($viewModel);

            $viewModel = new ViewModel($params);
            $viewModel->setTemplate($layout);
        }

        return $this->renderer->render($viewModel);
    }
}
