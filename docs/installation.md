# Installation

The recommended way to install this module is by using composer.

    composer require acelaya/zf2-acmailer

If you have the [laminas/laminas-component-installer](https://github.com/laminas/laminas-component-installer) package installed, it will ask you to enable the module, both in Mezzio and Laminas MVC. Otherwise, add the module to the list.

In Laminas MVC:

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

In Mezzio:

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
