## AcMailer

[![Build Status](https://travis-ci.org/acelaya/ZF2-AcMailer.svg?branch=master)](https://travis-ci.org/acelaya/ZF2-AcMailer)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/ZF2-AcMailer/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/acelaya/zf2-acmailer/v/stable.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![Total Downloads](https://poser.pugx.org/acelaya/zf2-acmailer/downloads.png)](https://packagist.org/packages/acelaya/zf2-acmailer)
[![License](https://poser.pugx.org/acelaya/zf2-acmailer/license.png)](https://packagist.org/packages/acelaya/zf2-acmailer)

This module, once enabled, registers a service with the key `AcMailer\Service\MailService` that wraps ZF2 mailing functionality, allowing to configure mail information to be used to send emails.
It supports file attachment and template email composition.

### Installation

Install composer in your project

    curl -s http://getcomposer.org/installer | php

Then run 

    php composer.phar require acelaya/zf2-acmailer:~4.0
    
Add the module `AcMailer` to your `config/application.config.php` file

```php
<?php

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'AcMailer',
        'Application'
    ),
    
[...]
```

### Usage

After installation, copy `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params. Configuration options are explained later.

Once you get the `AcMailer\Service\MailService` service, a new MailService instance will be returned and you will be allowed to set the body, set the subject and then send the message.

```php
$mailService = $serviceManager->get('AcMailer\Service\MailService');
$mailService->setSubject('This is the subject')
            ->setBody('This is the body'); // This can be a string, HTML or even a zend\Mime\Message or a Zend\Mime\Part

$result = $mailService->send();
if ($result->isValid()) {
    echo 'Message sent. Congratulations!';
} else {
    if ($result->hasException()) {
        echo sprintf('An error occurred. Exception: \n %s', $result->getException()->getTraceAsString());
    } else {
        echo sprintf('An error occurred. Message: %s', $result->getMessage());
    }
}
```

##### Rendering views

Alternatively, the body of the message can be set from a view script by using `setTemplate` instead of `setBody`. It will use a renderer to render defined template and then set it as the email body internally.

You can set the template as a string and pass the arguments for it.

```php
[...]

$mailService = $serviceManager->get('AcMailer\Service\MailService');
$mailService->setSubject('This is the subject')
            ->setTemplate('application/emails/merry-christmas', array('name' => 'John Doe', 'date' => date('Y-m-d'));

[...]
```

You can also set the template as a `Zend\View\Model\ViewModel` object, which will render child templates too.

```php
[...]

$mailService = $serviceManager->get('AcMailer\Service\MailService');

$layout = new \Zend\View\Model\ViewModel(array(
    'name' => 'John Doe',
    'date' => date('Y-m-d')
));
$layout->setTemplate("application/emails/merry-christmas");

$footer = new \Zend\View\Model\ViewModel();
$footer->setTemplate("application/emails/footer");

$layout->addChild($footer, "footer");

$mailService->setSubject('This is the subject')
            ->setTemplate($layout);

[...]
```

The renderer can be changed to another one (ie. Twig or Blade). It just needs to implement `Zend\View\Renderer\RendererInterface`.

By default AcMailer uses the default `ViewRenderer` service via an alias, `mailviewrenderer`. You can override that alias in your `service_manager` configuration in order to change the renderer service to be used:

```php

return array(
    'service_manager' => array(
        'aliases' => array(
            'mailviewrenderer' => 'ZfcTwigRenderer',
        ),
    ),
);
```

Alternatively you can just set it via setter: `$mailService->setRenderer($renderer);`.

##### Attachments

Files can be attached to the email before sending it by providing their paths with `addAttachment`, `addAttachments` or `setAttachments` methods.
At the moment we call `send`, all the files that already exist will be attached to the email.

```php
[...]

$mailService->addAttachment('data/mail/attachments/file1.pdf');
$mailService->addAttachment('data/mail/attachments/file2.pdf'); // This will add the second file to the attachments list

// Add two more attachments to the list
$mailService->addAttachments(array(
    'data/mail/attachments/file3.pdf',
    'data/mail/attachments/file4.pdf'
));
// At this point there is 4 attachments ready to be sent with the email

// If we call this, all previous attachments will be discarded
$mailService->setAttachments(array(
    'data/mail/attachments/another-file1.pdf',
    'data/mail/attachments/another-file2.pdf'
));

// A good way to remove all attachments is to call this
$mailService->setAttachments(array());

[...]
```

**Attention!!** Be careful when attaching files to your email programatically.

Attached images can be displayed inmail by setting the `cid` to the image filename in the image tag like this (thanks to [omarev](https://github.com/acelaya/ZF2-AcMailer/pull/32)).

```html
<img alt="This is an attached image" src="cid:image-filename.jpg">
```

##### Customize the Message

If mail options does not fit your needs or you need to update them at runtime, the message wrapped by the MailService can be customized by getting it before calling `send()`.

```php
$message = $mailService->getMessage();
$message->addTo("foobar@example.com")
        ->addTo("another@example.com")
        ->addBcc("hidden@domain.com");

$result = $mailService->send();
```

If you are using a `Zend\Mail\Transport\File` as the transport object and need to change any option at runtime do this

```php
[...]

$mailService = $serviceManager->get('AcMailer\Service\MailService');
$mailService->getTransport()->getOptions()->setPath('dynamically/generated/folder');
$result = $mailService->send();

[...]
```

### Configuration options

The mail service can be automatically configured by using the provided global configuration file. Supported options are fully explained at that file. This is what they are for.

- **mail_adapter**: Tells the mail service what type of transport adapter should be used. Any object or classname implementing `Zend\Mail\Transport\TransportInterface` is valid.
- **mail_adapter_service**: A service name to be used to get the transport object, in case standard transport configuration does not fit your needs. If defined, the **mail_adapter** option will be ignored.
- **from**: From email address.
- **from_name**: From name to be displayed.
- **to**: It can be a string with one destination email address or an array of multiple addresses.
- **cc**: It can be a string with one carbon copy email address or an array of multiple addresses.
- **bcc**: It can be a string with one blind carbon copy email address or an array of multiple addresses.
- **subject**: Default email subject.
- **body**: Default body to be used. Usually this will be generated at runtime, but can be set as a string at config file. It can contain HTML too.
- **template**: Array with template configuration. It has 4 child options.
    - *use_template*: True or false. Tells if template should be used, making the **body** option to be ignored.
    - *path*: Path of the template. The same used while setting the template of a ViewModel (ie. 'application/index/list').
    - *params*: Array with key-value pairs with parameters to be sent to the template.
    - *children*: Array with children templates to be used within the main template (layout). Each one of them can have its own children. Look at `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` for details.
- **attachments**: Allows to define an array of files that will be attached to the message, or even a directory that will be iterated to attach all found files.
    - *files*: Array of files to be attached
    - *dir*: Directory to iterate.
        - *iterate*: If it is not true, the directory won't be iterated.
        - *path*: The path of the directory to iterate looking for files.
        - *recursive*: True or false. Tells if nested directories should be iterated too.
- **server**: IP address or server name to be used while using a SMTP server. Only used for SMTP transport.
- **port**: SMTP server port while using SMTP transport.
- **smtp_user**: Username to be used for authentication against SMTP server. If none is provided the `from` option will be used for this purpose.
- **smtp_password**: Password to be used for authentication against SMTP server.
- **ssl**: Defines type of connection encryption against SMTP server. Values are `false` to disable SSL, and 'ssl' or 'tls'.
- **connection_class**: The connection class used for authentication while using SMTP transport. Values are 'smtp', 'plain', 'login' or 'crammd5'
- **file_path**: Directory where the email will be saved while using a File transport.
- **file_callback**: Callback used to get the filename while using File transport.

Many of the configuration options are specific for standard transport objects. If you are using a custom `Zend\Mail\Transport\TransportInterface` implementation, you can set the **mail_adapter_service** instead of the **mail_adapter** option, to define the service which returns the transport object.

### Event management

This module comes with a built-in event system.
- An event is triggered before the mail is sent (`MailEvent::EVENT_MAIL_PRE_SEND`).
- If everything was OK another event is triggered (`MailEvent::EVENT_MAIL_POST_SEND`) after the email has been sent.
- If an error occured, an error event is triggered (`MailEvent::EVENT_MAIL_SEND_ERROR`).

Managing mail events is as easy as extending `AcMailer\Event\AbstractMailListener`. It provides the `onPreSend`, `onPostSend` and `onSendError` methods, which get a `MailEvent` parameter that can be used to get the MailService which triggered the event.

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

### Testing

`AcMailer\Service\MailService` should be injected into Controllers or other Services which you probably need to test. It implements `AcMailer\Service\MailServiceInterface` for this purpose, but even a `MailServiceMock` is included.
It allows user to define if the message should or should not fail when `send` method is called, by calling `setForceError` method.
You can even know if `send` method was called after any action by calling `isSendMethodCalled`.

```php
[...]

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

[...]
```
