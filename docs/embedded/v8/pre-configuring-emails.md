# Pre-configuring emails

It is very likely that some of the emails of your system have always the same structure. AcMailer allows you to pre-configure those emails, so that you can then reference to them by their name.

Pre-configured emails have to be defined under the `acmailer_options.emails` configuration entry.

```php
<?php

declare(strict_types=1);

return [

    'acmailer_options' => [
        'emails' => [

            'base' => [
                'from' => 'no-reply@mycompany.com',
                'from_name' => 'My company',
            ],

            'welcome' => [
                'extends' => 'base',
                'subject' => 'Welcome to our service!',
                'template' => 'App::emails/welcome',
            ],

            'support' => [
                'extends' => 'base',
                'subject' => 'Support request received',
                'template' => 'App::emails/support',
            ],

        ],

        // ...
    ],

];
```

Once you have defined pre-configured emails, you can send any of them just by referencing to their name, and also provide any non-static option.

```php
<?php

declare(strict_types=1);

try {
    $result = $mailService->send('welcome', [
        'to' => ['new-user@gmail.com'
    ]]);

    if ($result->isValid()) {
        // Email properly sent
    }
} catch (AcMailer\Exception\MailException $e) {
    // Error sending email
}
```

> See the full configuration documentation [here](/configuration-options).
