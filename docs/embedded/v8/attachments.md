# Attachments

Files can be attached to the email before sending it, using different approaches.

```php
<?php

declare(strict_types=1);

$mailService->send(
    (new AcMailer\Model\Email())->addAttachment('data/mail/attachments/file1.pdf')
                                ->addAttachment(
                                    'data/mail/attachments/file2.pdf',
                                    'different-filename.pdf'
                                )
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

Attached images can be displayed inline by setting the `cid` to the image filename in the image tag like this. The alternative name should be used if provided.

```html
<img alt="This is an attached image" src="cid:image-filename.jpg">
```

## Attachment strategies

By default, 4 strategies are supported to attach files

* Providing the path as string.
* Providing a `resource`.
* Providing a `Laminas\Mime\Part` object.
* Providing an array which defines the value of the fields in the `Laminas\Mime\Part` object.

```php
<?php

declare(strict_types=1);

$mailService->send([
    'attachments' => [
        \fopen('data/mail/attachments/file1.pdf', 'r+b'),
        new Laminas\Mime\Part(\fopen('data/mail/attachments/file2.zip', 'r+b')),
        [
            'id' => 'something',
            'filename' => 'something_else',
            'content' => \file_get_contents('data/mail/attachments/file2.pdf'), // A resource can be used here too
            'encoding' => Laminas\Mime\Mime::ENCODING_7BIT, // Defaults to Laminas\Mime\Mime::ENCODING_BASE64
        ],
    ],
]);
```

> The array approach is very useful when you want to [pre-configure](/pre-configuring-emails) the files to be attached to an email.

## Custom attachment parsers

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
use Laminas\Mime;

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

As you can see, attachment parsers have to implement `AcMailer\Attachment\Parser\AttachmentParserInterface`

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

> The `attachment_parsers` configuration entry has a dependencies-like structure, where you can define factories, aliases, etc.

Finally, you just need to remember to attach files using the `AcMailer\Model\Attachment` wrapper, which allows you to define not only the attachment value but the parser which has to process it.

```php
<?php

declare(strict_types=1);

use AcMailer\Model;
use App\Mail\Attachment\FlysystemAttachmentParser;

$mailService->send(
    (new Model\Email())->addAttachment(
        new Model\Attachment(
            FlysystemAttachmentParser::class,
            'data/mail/attachments/file1.pdf'
        )
    )
);
```

If you want to pre-configure attachments which use a custom parser, you need to use a special array notation, where you specify the **parser_name** and the **value** of the attachment, like this:

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
                FlysystemAttachmentParser::class => function () {/* ... */},
            ],
        ],

    ],

];
```
