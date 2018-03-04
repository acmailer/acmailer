# Installation

The recommended way to install this module is by using composer.

    composer require acelaya/zf2-acmailer

If you have the [zendframework/zend-component-installer](https://github.com/zendframework/zend-component-installer) package installed, it will ask you to enable the module, both in ZF and Expressive. Otherwise, add the module to the list.

In Zend MVC:

```php
<?php
declare(strict_types=1);

return [
    'modules' => [
        // ...

        'Application',
        'AcMailer',

        // ...
    ],
];
```

In Zend Expressive:

```php
<?php
declare(strict_types=1);

$aggregator = new ConfigAggregator([
    // ...

    App\ConfigProvider::class,
    AcMailer\ConfigProvider::class,

    // ...
], '...');
```
