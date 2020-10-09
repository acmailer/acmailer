# Events management

This module comes with a built-in events system, which lets you hook your code to certain points in the email sending flow:

- An event is triggered before the email's template is rendered, if any (`AcMailer\Event\PreRenderEvent`).
- Another one is triggered before the email is sent, but after the body has been set (`AcMailer\Event\PreSendEvent`).
- If everything was OK, another event is triggered (`AcMailer\Event\PostSendEVent`) after the email has been sent.
- If any `Throwable` is thrown while sending the email, an error event is triggered (`AcMailer\Event\SendErrorEvent`), which wraps it.

Managing mail events is as easy as extending `AcMailer\Event\AbstractMailListener`. It provides the `onPreRender`, `onPreSend`, `onPostSend` and `onSendError` methods, which get the corresponding event objects, composing the sent `AcMailer\Model\Email` object and, in some cases, the produced `AcMailer\Result\MailResult`.

You can also just implement `AcMailer\Event\MailListenerInterface`, but in that case, you will have to implement the four methods.

When you attach listener objects to a `MailService`, the corresponding method will be automatically invoked when calling the `send` method on that service.

```php
<?php

declare(strict_types=1);

$mailListener = new Application\Event\MyMailListener();
$mailService->attachMailListener($mailListener);
```

> The `attachMailListener` method accepts a second optional int parameter which is the priority.

You can also pre-register listener service names in the service configuration, by providing service names, listener instances or an array notation which allows you to also define the listener priority:

```php
<?php

declare(strict_types=1);

return [
    
    'acmailer_options' => [
        'mail_services' => [
            'default' => [
                // ...
                'mail_listeners' => [
                    Application\Event\MyMailListener::class,
                    Application\Event\LogMailsListener::class,
                    [
                        'listener' => Application\Event\SomeMailsListener::class,
                        'priority' => 10,
                    ],
                ],
            ],
        ],
    ],
    
];
```

All event listeners registered as services will be lazily created when used. If no emails are sent, the listeners won't even be created.

The value returned by any of the listeners methods is ignored, except on the case of `onPreSend`. If that method returns a boolean `false`, the email sending will be cancelled.

```php
<?php

declare(strict_types=1);

$mailService->attachMailListener(new class extends AcMailer\Event\AbstractMailListener {
    public function onPreSend(AcMailer\Event\PreSendEvent $e)
    {
        // Do not allow emails to be sent to gmail
        foreach ($e->getEmail()->getTo() as $address) {
            if (stripos($address, 'gmail') !== false) {
                return false;
            }
        }
        
        return true;
    }
});

// If "throw_on_cancel" is true, a MailCancelledException will be thrown here. Otherwise, $result->isCancelled() will return true
$result = $mailService->send('contact', ['to' => ['me@gmail.com']]);
```

If you need to configure any service that could cause the email's template to produce a different result (like on localized emails), do it on the `onPreRender` method.

```php
<?php

declare(strict_types=1);

$mailService->attachMailListener(new class extends AcMailer\Event\AbstractMailListener {
    private $translator;

    public function onPreRender(AcMailer\Event\PreRenderEvent $e)
    {
        $this->translator->setLocale(/* Get locale somehow */);
    }
});
```

If, for example, the same translator is being used to translate templates, this is the proper place to set the locale, so that the template gets rendered in the correct language.
