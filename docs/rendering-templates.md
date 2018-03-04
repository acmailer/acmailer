# Rendering templates

Instead of setting a plain string, the body of the message can be composed by a template by using the `template` option instead of the `body` option.

You can also pass params to the template using the `template_params` option.

All MailServices compose a `Zend\Expressive\Template\TemplateRendererInterface` instance, which is internally used to render defined template.

In Expressive applications the `Zend\Expressive\Template\TemplateRendererInterface` service will be used, and in MVC, a very simple wrapper is included that composes the zend/view renderer.

The rendered template can use a layout. When using twig or plates renderers, you can use their own way to extend from layouts. When using zend/view, you can provide a "layout" param, with the name of the layout.

```php
<?php
declare(strict_types=1);

$mailService->send('contact', ['template_params' => [
    'layout' => 'application/mail/layout',
]]);
```

In Zend MVC, the renderer that is internally used can be changed to another one (like Twig or Blade). It just needs to implement `Zend\View\Renderer\RendererInterface`.

By default AcMailer uses the default `ViewRenderer` service via an alias, `mailviewrenderer`. You can override that alias in your `service_manager` configuration in order to change the renderer service to be used (thanks to [kusmierz](https://github.com/kusmierz)):

```php
<?php
declare(strict_types=1);

return [
    'service_manager' => [
        'aliases' => [
            'mailviewrenderer' => 'ZfcTwigRenderer',
        ],
    ],
];
```

If you need different view renderers to be used by each mail service, you can define the renderer service name in the **renderer** configuration property of that service. It has to be a service name that resolves to a `Zend\Expressive\Template\TemplateRendererInterface` instance.

```php
<?php
declare(strict_types=1);

return [
    'acmailer_options' => [
        'mail_services' => [
            'default' => [
                // ...
                'renderer' => 'ZfcTwigRenderer',
            ],
        ],
    ],
];
```

Also, when using zendframework/zend-mvc-console to run Zend MVC apps from the console, a renderer is created from scratch, honoring your `view_manager` and `view_helpers` configurations.

When an email is rendered from a template, the `AcMailer\Model\Email` object wrapped in the result and passed to event listeners, will have the result of that rendering in its `body` property in all events but `pre-render`, so calling `$email->getBody()` will return the generated HTML as a string.

> See [Events management](/events-management) for more information on how to manage mail events.
