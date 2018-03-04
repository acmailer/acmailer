# Usage

After installation, copy `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

As with any other MVC or Expressive configuration, you can choose to put any of the settings into a local configuration file so you can make environment-specific mail settings, and avoid sending credentials to version control systems.

By default, a service with name `acmailer.mailservice.default` will be registered for you, which is also aliased by the service names `AcMailer\Service\MailService`, `AcMailer\Service\MailServiceInterface` and `mailservice`.

All the services in the `acmailer.mailservice` namespace will return `AcMailer\Service\MailService` instances. The last part is the specific name, so that you can configure multiple mail services, each one with its own transport configuration.

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
    
    public function sendContactAction(): Zend\View\Model\ViewModel
    {
        $result = $this->mailService->send('contact');
        return new Zend\View\Model\ViewModel(['result' => $result]);
    }
}
```

## Send emails

There are different ways to send emails:

* By using the name of a preconfigured email (as in previous example). In this case you can even provide configuration options which will override the preconfigured one.

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
