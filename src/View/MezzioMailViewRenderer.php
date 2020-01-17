<?php

declare(strict_types=1);

namespace AcMailer\View;

use Mezzio\Template\TemplateRendererInterface;

class MezzioMailViewRenderer implements MailViewRendererInterface
{
    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(string $name, array $params = []): string
    {
        return $this->renderer->render($name, $params);
    }
}
