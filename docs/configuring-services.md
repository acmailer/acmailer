# Configuring services

We have seen how to configure and send emails, but we also need to configure the services that send them.

The configuration for all mail services has to be defined under the `acmailer_options.mail_services` configuration entry.

```php
<?php
declare(strict_types=1);

return [

    'acmailer_options' => [
        'mail_services' => [

            'default' => [
                'transport' => 'smtp',
                'transport_options' => [
                    'host' => 'smtp.gmail.com',
                    'port' => 587,
                    'connection_class' => 'login',
                    'connection_config' => [
                        'username' => 'my-email@gmail.com',
                        'password' => 'foobar',
                        'ssl' => 'tls',
                    ],
                ],
            ],

            'mycompany' => [
                'transport' => 'smtp',
                'transport_options' => [
                    'host' => 'smtp.mycompany.com',
                    'port' => 25,
                    'connection_class' => 'login',
                    'connection_config' => [
                        'username' => 'no-reply@mycompany.com',
                        'password' => 'foobar',
                    ],
                ],
            ],

            // Define other services here
        ],
    ],

];
```

> See the full configuration documentation [here](/configuration-options).

Then, every service can be fetched using the same formula to compose the service name. Three parts, separated by dots. The first one is **acmailer**, the second one is **mailservice**, and the third one is the specific service name, for example **default** or **mycompany**.

In the example, service names would be **acmailer.mailservice.default** and **acmailer.mailservice.mycompany**.

All services will work from scratch, since this module registers an abstract factory that creates them. However, it is recommended to explicitly register them as factories, which is more efficient (the **default** service comes already preregistered as factory).

```php
<?php
declare(strict_types=1);

use AcMailer\Service\Factory\MailServiceAbstractFactory;

return [
    
    'services_manager' => [ // 'dependencies' in the case of Expressive
        'factories' => [
            'acmailer.mailservice.mycompany' => MailServiceAbstractFactory::class,
        ],
    ],
    
];
```
