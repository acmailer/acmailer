<?php
declare(strict_types=1);

namespace AcMailer\View;

interface MailViewRendererInterface
{
    public function render(string $name, array $params = []): string;
}
