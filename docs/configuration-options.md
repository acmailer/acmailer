# Configuration options

**Important!** The configuration has completely changed in v7.x and is not compatible with earlier versions. If you want to upgrade, please, read this section, the [upgrade guide](https://github.com/acelaya/ZF-AcMailer/blob/master/UPGRADE.md#upgrade-from-5x6x-to-7x), and take a look at [this tool](https://github.com/acelaya/zf-acmailer-tooling) that can automatically migrate from older configurations to the new one.

An example configuration file is provided in `vendor/acelaya/zf2-acmailer/config/mail.global.php.dist` that comes with one example email and service already defined.

## Emails

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
- **custom_headers**: A key/value list with the custom headers you want to add to the email.

## Mail services

- **extends**: Defines other service from which to extend configuration, so that you only need to define the configuration that is different. By default this is null, which means that no configuration is extended.
- **transport**: Tells the mail service which type of transport adapter should be used. Any instance or class name implementing `Zend\Mail\Transport\TransportInterface` is valid. It is also possible to define a service and it will be automatically fetched.
- **transport_options**: Wraps the SMTP or File configuration that is used when the mail adapter is a `Zend\Mail\Transport\Smtp` or `Zend\Mail\Transport\File` instance. This option is ignored when a plain transport instance or a service name were provided for the **transport** option.
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
