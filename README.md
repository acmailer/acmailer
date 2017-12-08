# AcMailer

[![Build Status](https://travis-ci.org/acelaya/ZF-AcMailer.svg?branch=master)](https://travis-ci.org/acelaya/ZF-AcMailer)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/acelaya/zf2-acmailer/v/stable.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![Total Downloads](https://poser.pugx.org/acelaya/zf2-acmailer/downloads.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![License](https://poser.pugx.org/acelaya/zf2-acmailer/license.png)](https://packagist.org/packages/acelaya/zf2-acmailer)

This module provides a way to easily send emails from Zend Framework and Zend Expressive applications. It allows you to preconfigure emails and transport configurations, and then send those emails at runtime.

You will be able to compose emails from templates, and easily attach files to that email using different strategies.

### Installation

* * *

The recommended way to install this module is by using composer

    composer require acelaya/zf2-acmailer

If you have the zendframework/zend-component-installer package installed, it will ask you to enable the module, both in ZF and Expressive. Otherwise, add the module to the list.

In Zend Framework:

```php
<?php

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
$aggregator = new ConfigAggregator([
    // ...
    App\ConfigProvider::class,
    AcMailer\ConfigProvider::class,
    // ...
], '');
```

> **IMPORTANT! Version notes** 
> * Version **7.0.0**: A deep refactoring of the module has been made, improving the code and simplifying the overall usage. As a consequence, some BC breaks have been introduced. Read the migration guide.
> * Version **6.0.0**: Support for ZF2 has been dropped and this module is now compatible with ZF3 only. If you need ZF2 support, stick with v5 of this module.
> * Version **5.0.0**: Important BC breaks have been introduced, so make sure not to update from earlier versions in production without reading this documentation first. It is possible to autogenerate the new configuration structure from the command line. Read the configuration section at the end of this document for more information.

### Usage

* * *

After installation, copy `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

As with any other MVC or Expressive configuration, you can choose to put any of the settings into a local configuration file so you can make environment-specific mail settings, and avoid sending credentials to version control.

Configuration options are explained later.

By default, a service with name `acmailer.mailservice.default` will be registered for you, which is also aliased by the service names `AcMailer\Service\MailService`, `AcMailer\Service\MailServiceInterface` and `mailservice`.

All the services in the `acmailer.mailservice` namespace will return `AcMailer\Service\MailService` instances. The last part is the specific name, so that you can configure multiple mail services, each one with its own transport configuration.

```php
<?php
class IndexControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mailService = $container->get('acmailer.mailservice.default');
        return new IndexController($mailService);
    }
}

class IndexController
{
    public function __construct(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }
    
    public function sendContactAction()
    {
        $result = $this->mailService->send('contact');
        return new ViewModel(['result' => $result]);
    }
}
```

#### Send emails

There are different ways to send emails. By using the name of a preconfigured email (as in previous example), by passing the configuration of an email as array, or by passing a `AcMailer\Model\Email` instance.

```php
<?php
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

### Preconfigure emails

It is very likely that some of the emails of your system have always the same structure. It is possible to preconfigure those emails, so that you can then reference to them by their name.

Preconfigured emails have to be defined under the `acmailer_options.emails` configuration entry.

```php
<?php
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
$mailService->send('contact', ['template_params' => [
    'layout' => 'application/mail/layout',
]]);
```

In Zend MVC, the renderer that is internally used can be changed to another one (like Twig or Blade). It just needs to implement `Zend\View\Renderer\RendererInterface`.

By default AcMailer uses the default `ViewRenderer` service via an alias, `mailviewrenderer`. You can override that alias in your `service_manager` configuration in order to change the renderer service to be used (thanks to [kusmierz](https://github.com/kusmierz)):

```php

return [
    'service_manager' => [
        'aliases' => [
            'mailviewrenderer' => 'ZfcTwigRenderer',
        ],
    ],
];
```

If you need different view renderers to be used by each mail service, you can define the renderer service name in the **renderer** configuration property of that service. It has to be a service name that resolves to a `Zend\Expressive\Template\TemplateRendererInterface`.

#### Rendering in CLI executions

When running a ZF2 application from the console, the default `ViewRenderer` service is not created. In that case a `Zend\View\Renderer\PhpRenderer` is created on the fly so that templates can be properly rendered.

It has access to `view_manager` and `view_helpers` configuration, so template resolution will properly work and view helpers (both standard and custom) will be accessible from rendered templates.

If you overriden the `mailviewrenderer` service alias with your own view renderer, then that will be used instead of creating one on the fly.

It is safe to use this module to send emails from cron jobs and such.

#### Email charset

The email body charset can be set in diferent ways.

**String body**: Use the second argument of the `setBody` method.

```php
$mailService->setBody('Hello!!', 'utf-8');
$mailService->setBody('<h1>Hello!!</h1>', 'utf-8');
```

**Template body**: Set a 'charset' property in the second argument of the `setTemplate` method.

```php
$mailService->setTemplate(new Zend\View\Model\ViewModel(), ['charset' => 'utf-8']);
$mailService->setTemplate('application/emails/my-template', [
    'charset' => 'utf-8',
    'date' => date('Y-m-d'),
    'foo' => 'bar',
]);
```

**Mime\Part body**: Either set it in the object before calling `setBody` or pass it as the second argument.

```php
$part = new Zend\Mime\Part();
$part->charset = 'utf-8';
$mailService->setBody($part);

// Providing a charset will overwrite the Mime\Part's charset
$mailService->setBody($part, 'utf-8');
```

#### Attachments

Files can be attached to the email before sending it by providing their paths with `addAttachment`, `addAttachments` or `setAttachments` methods.
At the moment we call `send`, all the files that already exist will be attached to the email.

```php
$mailService->addAttachment('data/mail/attachments/file1.pdf');
$mailService->addAttachment('data/mail/attachments/file2.pdf', 'different-filename.pdf');

// Add two more attachments to the list
$mailService->addAttachments([
    'another-name.pdf' => 'data/mail/attachments/file3.pdf',
    'data/mail/attachments/file4.zip'
]);
// At this point there is 4 attachments ready to be sent with the email

// If we call this, all previous attachments will be discarded
$mailService->setAttachments([
    'data/mail/attachments/another-file1.pdf',
    'name-to-be-displayed.png' => 'data/mail/attachments/another-file2.png'
]);

// A good way to remove all attachments is to call this
$mailService->setAttachments([]);
```

The files will be attached with their real name unless you provide an alternative name as the key of the array element in `addAttachments` and `setAttachments` or as the second argument in `addAttachment`.

**Attention!!** Be careful when attaching files to your email programatically.

Attached images can be displayed inmail by setting the `cid` to the image filename in the image tag like this (thanks to [omarev](https://github.com/acelaya/ZF2-AcMailer/pull/32)). The alternative name should be used if provided.

```html
<img alt="This is an attached image" src="cid:image-filename.jpg">
```

Files can be attached as strings, which will be parsed as file paths, but resources, arrays or `Zend\Mime\Part` objects can be provided too.

```php
// Attach file as resource
$mailService->addAttachment(fopen('data/mail/attachments/file1.pdf', 'r+b'));

// Attach multiple files
$mailService->addAttachments([
    'another-name.pdf' => fopen('data/mail/attachments/file3.pdf', 'r+b'),
    new Zend\Mime\Part(fopen('data/mail/attachments/file4.zip', 'r+b')),
]);

// Attach a file as an array which properties will be mapped into a Zend\Mime\Part object
$mailService->addAttachment([
    'id' => 'something',
    'filename' => 'something_else',
    'content' => file_get_contents('data/mail/attachments/file2.pdf'), // A resource can be used here too
    'encoding' => Zend\Mime\Mime::ENCODING_7BIT, // Defaults to Zend\Mime\Mime::ENCODING_BASE64
]);
```

The array attachment approach is very useful when you want to preconfigure the files to be attached to an email.

#### Customize the Message

If mail options does not fit your needs or you need to update them at runtime, the message wrapped by the MailService can be customized by getting it before calling `send()`.

```php
$message = $mailService->getMessage();
$message->setSubject('This is the subject')
        ->addTo('foobar@example.com')
        ->addTo('another@example.com')
        ->addBcc('hidden@domain.com');

$result = $mailService->send();
```

If you are using a `Zend\Mail\Transport\File` as the transport object and need to change any option at runtime do this

```php
$mailService = $serviceManager->get('acmailer.mailservice.default');
$mailService->getTransport()->getOptions()->setPath('dynamically/generated/folder');
$result = $mailService->send();
```

### Event management

* * *

This module comes with a built-in event system.
- An event is triggered before the mail is sent (`MailEvent::EVENT_MAIL_PRE_SEND`).
- If everything was OK another event is triggered (`MailEvent::EVENT_MAIL_POST_SEND`) after the email has been sent.
- If an error occured, an error event is triggered (`MailEvent::EVENT_MAIL_SEND_ERROR`).

Managing mail events is as easy as extending `AcMailer\Event\AbstractMailListener`. It provides the `onPreSend`, `onPostSend` and `onSendError` methods, which get a `MailEvent` parameter that can be used to get the `MailService` which triggered the event or the `MailResult` produced.

Then attach the listener object to the `MailService` and the corresponding method will be automatically called when calling the `send` method.

```php
$mailListener = new \Application\Event\MyMailListener();
$mailService->attachMailListener($mailListener);

$mailService->send(); // Mail events will be triggered at this moment
```

If you want to detach a previously attached event manager, just call detach MailListener like this.

```php
$mailListener = new \Application\Event\MyMailListener();
$mailService->attachMailListener($mailListener);

// Some conditions occurred which made us not to want the events to be triggered any more on this listener
$mailService->detachMailListener($mailListener);

$mailService->send(); // The events on the $mailListener won't be triggered.
```

The `MailResult` will always be null when the event `EVENT_MAIL_PRE_SEND` is triggered, since the email hasn't been sent yet.

Any `Zend\Mail` exception will be catched, producing a `EVENT_MAIL_SEND_ERROR` instead. If any other kind of exception occurs, the same event will be triggered, but the exception will be rethrown in the form of an `AcMailer\Exception\MailException`. The event's wrapped exception will be the original exception.

### Configuration options

* * *

**Important!** The configuration has completly changed from v5.0.0 and is not compatible with earlier versions. If you want to upgrade, please, read this section.

When the mail service is requested, it automatically tries to find the `acmailer_options` config key under the global configuration, and then the specific name inside it. For example, if you fetch the `acmailer.mailservice.employees` service, the abstract factory will try to find the `employees` key under the `acmailer_options` block. If it is found, a MailService instance will be returned preconfigured with that configuration block.

An example configuration file is provided in `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` that comes with the `default` service already defined.

Each concrete service configuration can define these properties:

- **extends**: Defines other configuration block from which this one extends, so that you only need to define the configuration that is different. By default this is null, which means that no configuration is extended.
- **mail_adapter**: Tells the mail service what type of transport adapter should be used. Any instance or classname implementing `Zend\Mail\Transport\TransportInterface` is valid. It is also possible to define a service and it will be automatically fetched.
- **transport**: It is an alias for the **mail_adapter** option. Just use one or another.
- **renderer**: It is the service name of the renderer to be used. By default, *mailviewrenderer* is used, which is an alias to the default *viewrenderer* service.
- **message_options**: Wraps message-related options
    - **from**: From email address.
    - **from_name**: From name to be displayed.
    - **reply_to**: The email address to reply to
    - **reply_to_name**: The name to reply to
    - **to**: It can be a string with one destination email address or an array of multiple addresses.
    - **cc**: It can be a string with one carbon copy email address or an array of multiple addresses.
    - **bcc**: It can be a string with one blind carbon copy email address or an array of multiple addresses.
    - **encoding**: Encoding of headers. It can be a string defining encoding ('utf-8', 'ascii', etc.).
    - **subject**: Default email subject.
    - **body**: Wraps body configuration, like template, content or charset
        - **content**: A string with the default content body, either in plan text or HTML. Use it for simple emails.
        - **charset**: Charset to be used in the email body. Default value is utf-8
        - **use_template**: Tells if the body should be created from a template. If true, the **template** options will be used, ignoring the **content** option. Default value is `false`.
        - **template**: Wraps the configuration to create emails from templates.
            - *path*: Path of the template. The same used while setting the template of a ViewModel (ie. 'application/index/list').
            - *params*: Array with key-value pairs with parameters to be sent to the template.
            - *children*: Array with children templates to be used within the main template (layout). Each one of them can have its own children that will be recursively rendered. Look at `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` for details.
            - *default_layout*: Wraps the information to set a default layout for all the templates
                - *path*: Path of the layout. The same used while setting the template of a ViewModel (ie. 'application/index/list'). Default value is null, so that a default layout is not used.
                - *params*: Array with key-value pairs with parameters to be sent to the layout. By default is an empoty array.
                - *template_capture_to*: Capture to value for each template inside this layout. Default value is 'content'.
    - **attachments**: Wraps the configuration of attachements.
        - *files*: Array of files to be attached. Can be an associative array where keys are attachment names and values are file paths or array representations of the Mime\Part.
        - *dir*: Directory to iterate.
            - *iterate*: If it is not true, the directory won't be iterated. Default value is false.
            - *path*: The path of the directory to iterate looking for files. This files will be attached with their real names.
            - *recursive*: True or false. Tells if nested directories should be recursively iterated too.
- **smtp_options**: Wraps the SMTP configuration that is used when the mail adapter is a `Zend\Mail\Transport\Smtp` instance.
    - **host**: IP address or server name of the SMTP server. Default value is 'localhost'.
    - **port**: SMTP server port. Default value is 25.
    - **connection_class**: The connection class used for authentication. Values are 'smtp', 'plain', 'login' or 'crammd5'
    - **connection_config**
        - *username*: Username to be used for authentication against the SMTP server. If none is provided the `message_options/from` option will be used for this purpose.
        - *smtp_password*: Password to be used for authentication against the SMTP server.
        - *ssl*: Defines the type of connection encryption against the SMTP server. Values are 'ssl', 'tls' or `null` to disable encryption.
- **file_options**: Wraps the files configuration that will be used when the mail adapter is a `Zend\Mail\Transport\File` instance
    - **path**: Directory where the email will be saved.
    - **callback**: Callback used to get the filename of the email.
- **mail_listeners**: An array of mail listeners that will be automatically attached to the service once created. They can be either `AcMailer\Event\MailListenerInterface` instances or strings that will be used to fetch a service if exists or lazily instantiate an object. This is an empty array by default.

### Testing

* * *

`AcMailer\Service\MailService` should be injected into Controllers or other Services which you probably need to test. It implements `AcMailer\Service\MailServiceInterface` for this purpose, but even a `MailServiceMock` is included.
It allows user to define if the message should or should not fail when `send` method is called, by calling `setForceError` method.
You can even know if `send` method was called after any action by calling `isSendMethodCalled`.

```php
$mailServiceMock = new \AcMailer\Service\MailServiceMock();
$mailServiceMock->isSendMethodCalled(); // This will return false at this point

// Force an error
$mailServiceMock->setForceError(true);
$result = $mailServiceMock->send();
$result->isValid(); // This will return false because we forced an error

$mailServiceMock->isSendMethodCalled(); // This will return true at this point

// Force a success
$mailServiceMock->setForceError(false);
$result = $mailServiceMock->send();
$result->isValid(); // This will return true in this case
```

* * *

Thanks to [JetBrains](https://www.jetbrains.com/) for their support to open source projects.

![PhpStorm](http://static.alejandrocelaya.com/img/logo_PhpStorm.png)

