# Rendering templates

Instead of setting a plain string, the body of the message can be composed by a template by using the `template` option instead of the `body` option.

You can also pass params to the template using the `template_params` option.

All MailServices compose a `AcMailer\View\MailViewRendererInterface` instance, which is internally used to render templates (Prior to v7.2, it used to compose a `Mezzio\Template\TemplateRendererInterface` instance instead).

In Mezzio applications the `Mezzio\Template\TemplateRendererInterface` service will be used, and in MVC, the standard `ViewRenderer` service will be used. Both will be wrapped in an instance implementing `AcMailer\View\MailViewRendererInterface`.

The rendered template can use a layout. When using twig or plates renderers, you can use their own way to extend from layouts. When using laminas/view, you can provide a "layout" param, with the name of the layout.

```php
<?php

declare(strict_types=1);

$mailService->send('contact', ['template_params' => [
    'layout' => 'application/mail/layout',
]]);
```

In Laminas MVC, the renderer that is internally used can be changed to another one (like Twig or Blade). It just needs to implement `Laminas\View\Renderer\RendererInterface`.

By default AcMailer uses the default `ViewRenderer` service via an alias, `mailviewrenderer`. You can override that alias in your `service_manager` configuration in order to change the renderer service to be used:

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

Also when using Laminas MVC, if you have defined a layout, the child template is captured to the `content` property, so you have to use `<?= $this->content ?>` in order to render the child template.

However, this behavior can be overwritten by providing a `child_template_name` param with the name you want to use.

### Define renderer by service

If you need different view renderers to be used by each mail service, you can define the renderer service name in the **renderer** configuration property of that service. It has to be a service name that resolves to a `Mezzio\Template\TemplateRendererInterface` instance. From v7.2, it can also resolve to a `Laminas\View\Renderer\RendererInterface` or `AcMailer\View\MailViewRendererInterface` instance.

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

Also, when using laminas/laminas-mvc-console to run Laminas MVC apps from the console, a renderer is created from scratch, honoring your `view_manager` and `view_helpers` configurations.

When an email is rendered from a template, the `AcMailer\Model\Email` object wrapped in the result and passed to event listeners, will have the result of that rendering in its `body` property in all events but `pre-render`, so calling `$email->getBody()` will return the generated HTML as a string.

> See [Events management](/events-management) for more information on how to manage mail events.
