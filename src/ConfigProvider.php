<?php
declare(strict_types=1);

namespace AcMailer;

class ConfigProvider
{
    public function __invoke()
    {
        $moduleConfig = include __DIR__ . '/../config/module.config.php';
        return [
            'dependencies' => $moduleConfig['service_manager'],
        ];
    }
}
