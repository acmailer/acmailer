## AcMailer

This module, once enabled, registers a service with the key `AcMailer\Service\MailService` that wraps ZF2 mailing functionality, allowing to configure mail information to be used to send emails.

### Installation

Install composer in your project

	curl -s http://getcomposer.org/installer | php
	
Define dependencies in your composer.json file

	{
    	"require": {
	        "acelaya/zf2-acmailer": "dev-master"
	    }
	}
	
Finally install dependencies

	php composer.phar install

### Usage

After installation, copy `module/AcMailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

Once you get the `AcMailer\Service\MailService` service, a new MailService instance will be returned and you will be allowed to set the body, set the subject and then send the message.

```php
	$mailService = $serviceManager->get('AcMailer\Service\MailService');
	$mailService->setSubject('This is the subject');
	$mailService->setBody('This is the body'); // This can be a string, HTML or even a zend\Mime\Message or a Zend\Mime\Part
	
	$result = $mailService->send();
	if ($result->isValid() 
		echo 'Message sent. Congratulations!';
	else
		echo 'An error occured. Exception message: ' . $result->getMessage();
```

If mail options does not fit your needs or you need to update them at runtime, the message wrapped by MailService can be customized by getting it before calling send method.

```php
	$message = $mailService->getMessage();
	$message->addTo("foobar@example.com")
			->addTo("another@example.com")
			->addBcc("hidden@domain.com");
			
	$result = $mailService->send();
```