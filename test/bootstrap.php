<?php

$vendorDir = __DIR__ . '/../vendor';

if (file_exists($file = $vendorDir . '/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__ . '/../../../vendor/autoload.php')) {
    require_once $file;
} else {
    throw new \RuntimeException('Composer autoload not found');
}
