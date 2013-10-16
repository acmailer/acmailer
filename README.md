## AcMailer

This module, once enabled, registers a service with the key `MailService` that wraps ZF2 mailing functionality, allowing to configure mail information to be used to send emails.

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

After installation, copy `module/AcMailer/config/mail.global.php.dist` to `config/autoload/mail.global.php` and customize any of the params.

Once you get the `MailService` service, a new MailService instance will be returned and you will be allowed to set the body, set the subject and then send the message.