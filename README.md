## AcMailer

This module, once enabled, registers a service with the key `AcMailer\Service\MailService` that wraps ZF2 mailing functionality, allowing to configure mail information to be used to send emails.
It supports file attachment and template email composition.

### Installation

Install composer in your project

	curl -s http://getcomposer.org/installer | php
	
Define dependencies in your composer.json file

```json
	{
    	"require": {
	        "acelaya/zf2-acmailer": "dev-master"
	    }
	}
```
	
Finally install dependencies

	php composer.phar install

### Usage

After installation, copy `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params. Configuration options are explained later.

Once you get the `AcMailer\Service\MailService` service, a new MailService instance will be returned and you will be allowed to set the body, set the subject and then send the message.

```php
	$mailService = $serviceManager->get('AcMailer\Service\MailService');
	$mailService->setSubject('This is the subject')
				->setBody('This is the body'); // This can be a string, HTML or even a zend\Mime\Message or a Zend\Mime\Part
	
	$result = $mailService->send();
	if ($result->isValid())
		echo 'Message sent. Congratulations!';
	else
		echo 'An error occured. Exception message: ' . $result->getMessage();
```

Alternatively, the body of the message can be set from a view script by using `setTemplate` instead of `setBody`. It will use a renderer to render defined template and then set it as the email body internally.

```php
	$mailService = $serviceManager->get('AcMailer\Service\MailService');
	$mailService->setSubject('This is the subject')
				->setTemplate('application/emails/merry-christmas', array('name' => 'John Doe', 'date' => date('Y-m-d'));
	
	[...]
```

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

If mail options does not fit your needs or you need to update them at runtime, the message wrapped by MailService can be customized by getting it before calling send method.

```php
	$message = $mailService->getMessage();
	$message->addTo("foobar@example.com")
			->addTo("another@example.com")
			->addBcc("hidden@domain.com");
			
	$result = $mailService->send();
```

### Configuration options

The mail service can be automatically configured by using provided global configuration file. Supported options are fully explained at that file. This is what they are for.

- **mail_adapter**: Tells mail service what type of transport adapter should be used. SMTP and Sendmail are supported and values for this option can be any of these:
	- `Zend\Mail\Transport\Sendmail`
	- `Sendmail`
	- `sendmail`
	- `Zend\Mail\Transport\Smtp`
	- `Smtp`
	- `smtp`
- **server**: IP address or server name to be used while using an SMTP server. Will be ignored while using Sendmail.
- **port**: SMTP server port while using SMTP server. Will be ignored while using Sendmail.
- **from**: From email address.
- **from_name**: From name to be displayed.
- **to**: It can be a string with one destination email address or an array of multiple addresses.
- **cc**: It can be a string with one carbon copy email address or an array of multiple addresses.
- **bcc**: It can be a string with one blind carbon copy email address or an array of multiple addresses.
- **smtp_user**: Username to be used for authentication against SMTP server. If none is provided the `from` option will be used for this purpose.
- **smtp_password**: Password to be used for authentication against SMTP server.
- **ssl**: Defines type of connection encryption against SMTP server. Values are `false` to disable SSL, and 'ssl' or 'tls'.
- **body**: Default body to be used. Usually this will be generated at runtime, but can be set as a string at config file. It can contain HTML too.
- **subject**: Default email subject.
- **template**: Array with template configuration. It has 3 child options.
	- *use_template*: True or false. Tells if template should be used, making body option to be ignored.
	- *path*: Path of the template. The same used while setting the template of a ViewModel ('application/index/list').
	- *params*: Array with key-value pairs with parameters to be sent to the template.
- **attachments_dir**: Path to a directory that will be recursively iterated. All found files will be attached to the email automatically. Will be ignored if it is not a string or is not an existing directory. This means that you could set it to `false` to disable this option.

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
	$result = $mailService->send();
	$result->isValid(); // This will return false because we forced an error
	
	$mailServiceMock->isSendMethodCalled(); // This will return true at this point
	
	// Force a success
	$mailServiceMock->setForceError(false);
	$result = $mailService->send();
	$result->isValid(); // This will return true in this case
	
	[...]
```
