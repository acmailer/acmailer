# AcMailer

[![Build Status](https://travis-ci.org/acelaya/ZF-AcMailer.svg?branch=master)](https://travis-ci.org/acelaya/ZF-AcMailer)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/acelaya/zf2-acmailer/v/stable.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![Total Downloads](https://poser.pugx.org/acelaya/zf2-acmailer/downloads.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![License](https://poser.pugx.org/acelaya/zf2-acmailer/license.png)](https://packagist.org/packages/acelaya/zf2-acmailer)

This module provides a way to easily send emails from Zend Framework and Zend Expressive applications. It allows you to preconfigure emails and transport configurations, and then send those emails at runtime.

You will be able to compose emails from templates, and easily attach files to those emails using different strategies.

> **IMPORTANT! Version notes**
> * You are reading the documentation for this component's **v7**. Access [here](https://github.com/acelaya/ZF-AcMailer/blob/master/UPGRADE.md#upgrade-from-5x6x-to-7x) to see how to upgrade from previous versions.
> * Access **v6** documentation [here](https://github.com/acelaya/ZF-AcMailer/tree/6.x).
> * Access **v5** documentation [here](https://github.com/acelaya/ZF-AcMailer/tree/5.x).

### Installation

* * *

The recommended way to install this module is by using composer

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

### Usage

* * *

After installation, copy `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

As with any other MVC or Expressive configuration, you can choose to put any of the settings into a local configuration file so you can make environment-specific mail settings, and avoid sending credentials to version control.

Configuration options are explained later.

By default, a service with name `acmailer.mailservice.default` will be registered for you, which is also aliased by the service names `AcMailer\Service\MailService`, `AcMailer\Service\MailServiceInterface` and `mailservice`.

All the services in the `acmailer.mailservice` namespace will return `AcMailer\Service\MailService` instances. The last part is the specific name, so that you can configure multiple mail services, each one with its own transport configuration.

```php
<?php
declare(strict_types=1);

class IndexControllerFactory
{
    public function __invoke($container)
    {
        $mailService = $container->get('acmailer.mailservice.default');
        return new IndexController($mailService);
    }
}

class IndexController
{
    public function __construct(AcMailer\Service\MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }
    
    public function sendContactAction()
    {
        $result = $this->mailService->send('contact');
        return new Zend\View\Model\ViewModel(['result' => $result]);
    }
}
```

#### Send emails

There are different ways to send emails. By using the name of a preconfigured email (as in previous example), by passing the configuration of an email as array, or by passing a `AcMailer\Model\Email` instance.

```php
<?php
declare(strict_types=1);

// Using an array
$result = $mailService->send([
    'from' => 'my@address.com',
    'to' => ['your@address.com'],
    'subject' => 'Greetings!',
    'body' => 'Hello!',
]);

// Using a model
$result = $mailService->send(
    (new AcMailer\Model\Email())->setFrom('my@address.com')
                                ->setTo(['your@address.com'])
                                ->setSubject('Greetings!')
                                ->setBody('Hello!')
);

// You can even use a preconfigured email, but override any option
$result = $mailService->send('contact', [
    'to' => ['your@address.com'],
]);
```

#### Preconfigure emails

It is very likely that some of the emails of your system have always the same structure. It is possible to preconfigure those emails, so that you can then reference to them by their name.

Preconfigured emails have to be defined under the `acmailer_options.emails` configuration entry.

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

Now, you can send any of those emails just by referencing to them by its name, and also provide any non-static option.

```php
<?php
declare(strict_types=1);

try {
    $result = $mailService->send('welcome', ['to' => ['new-user@gmail.com']]);
    if ($result->isValid()) {
        // Email properly sent
    }
} catch (AcMailer\Exception\MailException $e) {
    // Error sending email
}
```

#### Rendering templates

Instead of setting a plain string, the body of the message can be set from a template by defining the `template` option instead of the `body` option.

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

Also, when using zendframework/zend-mvc-console to run Zend MVC apps from the console, a renderer is created from scratch, honoring your `view_manager` and `view_helpers` configurations.

When an email is rendered from a template, the `AcMailer\Model\Email` object wrapped in the result and passed to event listeners, will have the result of that rendering in its `body` property, so calling `$email->getBody()` will return the generated HTML as a string.

#### Attachments

Files can be attached to the email before sending it by providing their paths with `addAttachment`, `addAttachments`, `setAttachments` or `setAttachmentsDir` methods.

```php
<?php
declare(strict_types=1);

$mailService->send(
    (new AcMailer\Model\Email())->addAttachment('data/mail/attachments/file1.pdf')
                                ->addAttachment('data/mail/attachments/file2.pdf', 'different-filename.pdf')
                                ->addAttachments([
                                    'another-name.pdf' => 'data/mail/attachments/file3.pdf',
                                    'data/mail/attachments/file4.zip',
                                ])
);

$mailService->send([
    'attachments' => [
        'data/mail/attachments/another-file1.pdf',
        'name-to-be-displayed.png' => 'data/mail/attachments/another-file2.png',
    ],
]);
```

The files will be attached with their real name unless you provide an alternative name as the key of the array element in `addAttachments` and `setAttachments` or as the second argument in `addAttachment`.

Attached images can be displayed inline by setting the `cid` to the image filename in the image tag like this (thanks to [omarev](https://github.com/acelaya/ZF2-AcMailer/pull/32)). The alternative name should be used if provided.

```html
<img alt="This is an attached image" src="cid:image-filename.jpg">
```

Files can be attached as strings, which will be parsed as file paths, but resources, arrays or `Zend\Mime\Part` objects can be provided too.

```php
<?php
declare(strict_types=1);

$mailService->send([
    'attachments' => [
        \fopen('data/mail/attachments/file1.pdf', 'r+b'),
        new Zend\Mime\Part(\fopen('data/mail/attachments/file2.zip', 'r+b')),
        [
            'id' => 'something',
            'filename' => 'something_else',
            'content' => \file_get_contents('data/mail/attachments/file2.pdf'), // A resource can be used here too
            'encoding' => Zend\Mime\Mime::ENCODING_7BIT, // Defaults to Zend\Mime\Mime::ENCODING_BASE64
        ],
    ],
]);
```

The array attachment approach is very useful when you want to preconfigure the files to be attached to an email.

**Custom attachment parsers**

If for some reason none of the attachment strategies fits your needs, you can register your own attachment parsers.

For example, imagine you want your attachments to be parsed using the [league/flysystem](https://flysystem.thephpleague.com/) package.

You could define your own attachment parser, like this:

```php
<?php
declare(strict_types=1);

namespace App\Mail\Attachment;

use AcMailer\Attachment\Parser\AttachmentParserInterface;
use AcMailer\Exception\InvalidAttachmentException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileNotFoundException;
use Zend\Mime;

class FlysystemAttachmentParser implements AttachmentParserInterface
{
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function parse($attachment, string $attachmentName = null): Mime\Part
    {
        if (! \is_string($attachment)) {
            throw InvalidAttachmentException::fromExpectedType('string');
        }

        try {
            $stream = $this->filesystem->readStream($attachment);
            $mimeType = $this->filesystem->getMimetype($attachment);
            $meta = $this->filesystem->getMetadata($attachment);
            $name = $attachmentName ?? \basename($meta['path']);
        } catch (FileNotFoundException $e) {
            throw new InvalidAttachmentException(\sprintf(
                'Provided attachment %s could not be found',
                $attachment
            ), -1, $e);
        }

        $part = new Mime\Part($stream);
        $part->id = $name;
        $part->filename = $name;
        $part->type = $mimeType;
        $part->encoding = Mime\Mime::ENCODING_BASE64;
        $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        return $part;
    }
}
```

Now you have to register the attachment parser, like this.

```php
<?php
declare(strict_types=1);

use App\Mail\Attachment\FlysystemAttachmentParser;
use League\Flysystem\FilesystemInterface;

return [

    'acmailer_options' => [

        // ...

        'attachment_parsers' => [
            'factories' => [
                FlysystemAttachmentParser::class => function ($container) {
                    $filesystem = $container->get(FilesystemInterface::class);
                    return new FlysystemAttachmentParser($filesystem);
                },
            ],
        ],

    ],

];
```

> The `attachment_parsers` configuration entry has a service manager-like structure, where you can define factories, aliases and such.

Finally, you just need to remember to attach files using the `AcMailer\Model\Attachment` wrapper, which allows you to define not only the attachment value but the parsers which has to process it.

```php
<?php
declare(strict_types=1);

use AcMailer\Model;
use App\Mail\Attachment\FlysystemAttachmentParser;

$mailService->send(
    (new Model\Email())->addAttachment(
        new Model\Attachment(FlysystemAttachmentParser::class, 'data/mail/attachments/file1.pdf')
    )
);
```

If you want to preconfigure attachments which use a custom parser, you need to use a special array notation, where you specify the attachmentParser and the value of teh attachment, like this:

```php
<?php
declare(strict_types=1);

use App\Mail\Attachment\FlysystemAttachmentParser;
use League\Flysystem\FilesystemInterface;

return [

    'acmailer_options' => [

        'emails' => [
            'contact' => [
                'attachments' => [
                    [
                        'parser_name' => FlysystemAttachmentParser::class,
                        'value' => 'data/mail/attachments/file1.pdf',
                    ],

                    // Other attachments...
                ],
            ],
        ],

        'attachment_parsers' => [
            'factories' => [
                FlysystemAttachmentParser::class => function ($container) {/* ... */},
            ],
        ],

    ],

];
```

#### Configure services

We have seen how to configure and send emails, but we also need to configure the services that send them.

The configuration for a mail services have to be defined under the `acmailer_options.mail_services` configuration entry.

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

Then, every service can be fetched using the same formula to compose the service name. Three parts, separated by dots. The first one is **acmailer**, the second one is **mailservice**, and the third one is the specific service name, for example **default** or **mycompany**.

In this case, service names would be **acmailer.mailservice.default** and **acmailer.mailservice.mycompany**.

All services will work from scratch, since this module registers an abstract factory that creates them. However, it is recommended to explicitly register them as factories, which is more efficient (the **default** service comes preregistered as factory too).

```php
<?php
declare(strict_types=1);

return [
    
    'services_manager' => [ // 'dependencies' in the case of Expressive
        'factories' => [
            'acmailer.mailservice.mycompany' => AcMailer\Service\Factory\MailServiceAbstractFactory::class,
        ],
    ],
    
];
```

### Event management

* * *

This module comes with a built-in event system.

- An event is triggered before the email's template is rendered, if any. (`MailEvent::EVENT_MAIL_PRE_RENDER`).
- Another one is triggered before the email is sent, but after the body has been set. (`MailEvent::EVENT_MAIL_PRE_SEND`).
- If everything was OK another event is triggered (`MailEvent::EVENT_MAIL_POST_SEND`) after the email has been sent.
- If any `Throwable` is thrown while sending the email, an error event is triggered (`MailEvent::EVENT_MAIL_SEND_ERROR`), which wraps it.

Managing mail events is as easy as extending `AcMailer\Event\AbstractMailListener`. It provides the `onPreRender`, `onPreSend`, `onPostSend` and `onSendError` methods, which get a `MailEvent` parameter which composes the sent `AcMailer\Model\Email` object and the produced `AcMailer\Result\MailResult`.

Then attach the listener object to the `MailService` and the corresponding method will be automatically called when calling the `send` method.

```php
<?php
declare(strict_types=1);

$mailListener = new Application\Event\MyMailListener();
$mailService->attachMailListener($mailListener);
```

You can also preregister listeners service names in the service configuration:

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
    public function onPreSend(AcMailer\Event\MailEvent $e)
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

    public function onPreRender(AcMailer\Event\MailEvent $e)
    {
        $this->translator->setLocale(/* Get locale somehow */);
    }
});
```

If, for example, the same translator is being used to translate templates, this is the proper place to set the locale, so that the template gets rendered in the correct language.

### Configuration options

* * *

**Important!** The configuration has completely changed in v7.x and is not compatible with earlier versions. If you want to upgrade, please, read this section, the [upgrade guide](https://github.com/acelaya/ZF-AcMailer/blob/master/UPGRADE.md#upgrade-from-5x6x-to-7x), and take a look at [this tool](https://github.com/acelaya/zf-acmailer-tooling) that can automatically migrate from older configurations to the new one.

An example configuration file is provided in `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` that comes with one example email and service already defined.

- **Emails**:
    - **extends**: Defines other email from which to extend configuration, so that you only need to define the configuration that is different. By default this is null, which means that no configuration is extended.
    - **from**: From email address as string.
    - **from_name**: From name to be displayed as string.
    - **reply_to**: The email address to reply to as string.
    - **reply_to_name**: The name to reply to as string.
    - **to**: An array of strings containing the email addresses to send the email to.
    - **cc**: An array of strings containing the email addresses to send the email to as carbon copy.
    - **bcc**: An array of strings containing the email addresses to send the email to as blind carbon copy.
    - **encoding**: Encoding of headers. It can be a string defining the encoding ('utf-8', 'ascii', etc.).
    - **subject**: Default email subject as string.
    - **body**: The body of the email as a plain-text string.
    - **template**: The name of the template to be used to render an html body. If this is defined, the body property will be ignored.
    - **template_params**: An array of params to send to the template. If you are using zend/view, you can provide a "layout" param here in order to define the layout in which the template should be wrapped.
    - **attachments**: An array of attachments to add to the email. If a string key is provided, it will be used as the name of the attachment, otherwise, the real filename will be used.
    - **attachments_dir**: Defines how to attach all files in a directory. It wraps two properties:
        - *path*: The path of the directory to iterate looking for files. This files will be attached with their real names.
        - *recursive*: True or false. Tells if nested directories should be recursively iterated too.
    - **charset**: The charset used on every part of the email. Defaults to 'utf-8'.

- **Mail services**:
    - **extends**: Defines other service from which to extend configuration, so that you only need to define the configuration that is different. By default this is null, which means that no configuration is extended.
    - **transport**: Tells the mail service which type of transport adapter should be used. Any instance or class name implementing `Zend\Mail\Transport\TransportInterface` is valid. It is also possible to define a service and it will be automatically fetched.
    - **transport_options**: Wraps the SMTP or File configuration that is used when the mail adapter is a `Zend\Mail\Transport\Smtp` or `Zend\Mail\Transport\File` instance.
        - *SMTP*
            - **host**: IP address or server name of the SMTP server. Default value is 'localhost'.
            - **port**: SMTP server port. Default value is 25.
            - **connection_class**: The connection class used for authentication. Values are 'smtp', 'plain', 'login' or 'crammd5'. Default value is 'smtp'
            - **connection_config**
                - *username*: Username to be used for authentication against the SMTP server.
                - *smtp_password*: Password to be used for authentication against the SMTP server.
                - *ssl*: Defines the type of connection encryption against the SMTP server. Values are 'ssl', 'tls' or `null` to disable encryption.
        - *File*
            - **path**: Directory where the email will be saved.
            - **callback**: Callback used to get the filename of the email.ª
    - **renderer**: It is the service name of the renderer to be used. By default, *mailviewrenderer* is used in Zend MVC apps (which is an alias to the default *ViewRenderer* service), and the `Zend\Expressive\Template\TemplateRendererInterface` is used in Expressive apps.
    - **mail_listeners**: An array of mail listeners that will be automatically attached to the service once created. They can be either `AcMailer\Event\MailListenerInterface` instances or strings that will be used to fetch a service if exists or lazily instantiate an object. This is an empty array by default.

* * *

Thanks to [JetBrains](https://www.jetbrains.com/) for their support to open source projects.

![PhpStorm](http://static.alejandrocelaya.com/img/logo_PhpStorm.png)

