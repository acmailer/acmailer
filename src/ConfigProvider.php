<?php

namespace AcMailer;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => (include __DIR__ . '/../config/module.config.php')['service_manager']
        ];
    }
}
