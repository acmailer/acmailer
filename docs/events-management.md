# Events management

This module comes with a built-in events system, which lets you hook your code to certain points in the email sending flow:

- An event is triggered before the email's template is rendered, if any (`AcMailer\Event\PreRenderEvent`, This event was added in v7.0.4).
- Another one is triggered before the email is sent, but after the body has been set (`AcMailer\Event\PreSendEvent`).
- If everything was OK, another event is triggered (`AcMailer\Event\PostSendEVent`) after the email has been sent.
- If any `Throwable` is thrown while sending the email, an error event is triggered (`AcMailer\Event\SendErrorEvent`), which wraps it.

Managing mail events is as easy as extending `AcMailer\Event\AbstractMailListener`. It provides the `onPreRender`, `onPreSend`, `onPostSend` and `onSendError` methods, which get the corresponding event objects, composing the sent `AcMailer\Model\Email` object and, in some cases, the produced `AcMailer\Result\MailResult`.

Alternatively, starting on version 7.1.0, if you need your listener to extend from another class, you can also just implement `AcMailer\Event\MailListenerInterface`, but in that case, remember to use the `AcMailer\Event\MailListenerTrait` too, which provides the event attachment boilerplate code which will make the listener properly work.

When you attach listener objects to a `MailService`, the corresponding method will be automatically called when calling the `send` method on that service.

```php
<?php
declare(strict_types=1);

$mailListener = new Application\Event\MyMailListener();
$mailService->attachMailListener($mailListener);
```

You can also preregister listener service names in the service configuration:

```php
<?php
return [
    
    'acmailer_options' => [
        'mail_services' => [
            'default' => [
                // ...
                'mail_listeners' => [
                    Application\Event\MyMailListener::class,
                    Application\Event\LogMailsListener::class,
                ],
            ],
        ],
    ],
    
];
```

All event listeners registered as services will be lazily created when used. If no emails are sent, the listeners won't even be created.

The value returned by any of the listeners methods is ignored, except on the case of `onPreSend`. If that method returns a boolean `false`, the email sending will be cancelled, and the returned `AcMailer\Result\MailResult` object will indicate it.

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

$result = $mailService->send('contact', ['to' => ['me@gmail.com']]);
var_dump($result->isCancelled()); // This will print true
```

If you need to configure any service that could cause the email's template to produce a different result (like on translatable emails), do it on the `onPreRender` method.

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
