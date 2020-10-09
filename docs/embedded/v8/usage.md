# Usage

After installation, copy the file `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

As with any other MVC or Mezzio configuration, you can choose to put any of the settings into a local configuration file, so you can make environment-specific mail settings, and avoid sending credentials to version control systems.

By default, a service with name `acmailer.mailservice.default` will be registered for you, which is also aliased by the service names `AcMailer\Service\MailService`, `AcMailer\Service\MailServiceInterface` and `mailservice`.

All the services in the `acmailer.mailservice` namespace will return `AcMailer\Service\MailService` instances. The last part is the specific name, so that you can configure multiple mail services, each one with its own configuration.

```php
<?php

declare(strict_types=1);

class IndexControllerFactory
{
    public function __invoke($container): IndexController
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
    
    public function sendContactAction(): Laminas\View\Model\ViewModel
    {
        $result = $this->mailService->send('contact');
        return new Laminas\View\Model\ViewModel(['result' => $result]);
    }
}
```

## Sending emails

There are different ways to send emails:

* By using the name of a pre-configured email (as in previous example). In this case you can even provide configuration options which will override the pre-configured one.

    ```php
    <?php
    
    declare(strict_types=1);
    
    $result = $mailService->send('contact', [
        'to' => ['your@address.com'],
    ]);
    ```

* By passing the configuration of an email as array.

    ```php
    <?php
    
    declare(strict_types=1);
    
    $result = $mailService->send([
        'from' => 'my@address.com',
        'to' => ['your@address.com'],
        'subject' => 'Greetings!',
        'body' => 'Hello!',
    ]);
    ```

* By passing an `AcMailer\Model\Email` instance.

    ```php
    <?php
    
    declare(strict_types=1);
    
    $result = $mailService->send(
        (new AcMailer\Model\Email())->setFrom('my@address.com')
                                    ->setTo(['your@address.com'])
                                    ->setSubject('Greetings!')
                                    ->setBody('Hello!')
    );
    ```

## Error handling

While sending an email, if an error occurs, the service will throw a `AcMailer\Exception\MailException`. It's usually a good idea to catch it.

```php
<?php

declare(strict_types=1);

try {
    $result = $mailService->send('welcome', [
        'to' => ['new-user@gmail.com'],
    ]);

    if ($result->isCancelled()) {
        // Email was cancelled by a PreSendEvent listener
    }
} catch (AcMailer\Exception\MailException $e) {
    // Error sending email
}
```

In order to simplify handling errors and make it more consistent, after v8.1.0, it is also possible to make cancelled events throw an exception, by setting the `throw_on_cancel` service config option with value `true`.

This option is `false` by default, but will be the default behavior once v9.0.0 is released, effectively deprecating previous behavior. 

```php
<?php

declare(strict_types=1);

try {
    $mailService->send('welcome', [
        'to' => ['new-user@gmail.com'],
    ]);

    // Email was properly sent
} catch (AcMailer\Exception\MailCancelledException $e) {
    // Email was cancelled by a PreSendEvent listener
} catch (AcMailer\Exception\MailException $e) {
    // Error sending email
}
```
