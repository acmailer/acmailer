# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]

#### Added

* [#234](https://github.com/acelaya/ZF-AcMailer/issues/234) Added PHP 7.4 to build matrix.

#### Changed

* [#235](https://github.com/acelaya/ZF-AcMailer/issues/235) Updated to shlink coding standard v2.

#### Deprecated

* *Nothing*

#### Removed

* [#227](https://github.com/acelaya/ZF-AcMailer/issues/227) Dropped support for PHP 7.1
* [#237](https://github.com/acelaya/ZF-AcMailer/issues/237) Dropped support for [zendframework/zend-expressive-template](https://github.com/zendframework/zend-expressive-template) v1.

#### Fixed

* *Nothing*


## 7.4.1 - 2019-05-27

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#232](https://github.com/acelaya/ZF-AcMailer/issues/232) Fixed notice threw when `MailServiceAbstractFactory::canCreate` method is called and the name cannot be split in 3 parts.


## 7.4.0 - 2019-05-26

#### Added

* [#229](https://github.com/acelaya/ZF-AcMailer/issues/229) Added support for mail services [dynamically configured at runtime](http://acelaya.github.io/ZF-AcMailer/#/configuring-services?id=dynamic-runtime-configuration).

#### Changed

* [#224](https://github.com/acelaya/ZF-AcMailer/issues/224) and [#228](https://github.com/acelaya/ZF-AcMailer/issues/228) Updated dev dependencies.

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* *Nothing*


## 7.3.3 - 2019-01-18

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#222](https://github.com/acelaya/ZF-AcMailer/issues/222) Fixed transport options being set on transports that have been configured as a plain instance or a service name. Now those transports are returned as they are in those two cases.


## 7.3.2 - 2018-12-05

#### Added

* [#211](https://github.com/acelaya/ZF-AcMailer/issues/211) Added PHP 7.3 to the build matrix.

#### Changed

* [#212](https://github.com/acelaya/ZF-AcMailer/issues/212) Performance and maintainability slightly improved by enforcing via code sniffer that all global namespace classes, functions and constants are explicitly imported.
* [#215](https://github.com/acelaya/ZF-AcMailer/issues/215) Updated infection to v0.11.
* [#216](https://github.com/acelaya/ZF-AcMailer/issues/216) Added dependency on [Shlinkio](https://github.com/shlinkio/php-coding-standard) coding standard.

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#218](https://github.com/acelaya/ZF-AcMailer/issues/218) Fixed `FilePathAttachmentParser` trying to acquire write permissions on provided attachment when it does not really need that permission, which could result in filesystem errors.


## 7.3.1 - 2018-09-02

#### Added

* [#207](https://github.com/acelaya/ZF-AcMailer/issues/207) Improved badges in readme file

#### Changed

* [#208](https://github.com/acelaya/ZF-AcMailer/issues/208) Updated to Infection 0.10

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* *Nothing*


## 7.3.0 - 2018-06-03

#### Added

* [#201](https://github.com/acelaya/ZF-AcMailer/issues/201) Added the ability to define custom email headers.

    Now you can easily add custom headers to pre-configured and inline emails.

    ```php
    // config
    [
        'custom_headers' => [
            'X-Company' => 'My company',
        ],
    ],


    // inline
    $mailService->send('my_email', ['custom_headers' => [
        'X-Company' => 'My company',
    ]]);
    $mailService->send((new Email())->setCustomHeaders([
        'X-Company' => 'My company',
    ]));
    ```

* [#204](https://github.com/acelaya/ZF-AcMailer/issues/204) Updated some of the changelog entries to use the [keepachangelog.com](https://keepachangelog.com) recommendation.

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* *Nothing*


## 7.2.1 - 2018-05-27

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#200](https://github.com/acelaya/ZF-AcMailer/issues/200) Fixed rendered layouts not receiving template params when using zend/renderer with zend-mvc


## 7.2.0 - 2018-03-18

#### Added

* [#197](https://github.com/acelaya/ZF-AcMailer/issues/197) Added support for Expressive 3 via zend expressive renderer v2
* [#198](https://github.com/acelaya/ZF-AcMailer/issues/198) Improved build matrix so that it passes build for lowest and latest supported dependencies

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* *Nothing*


## 7.1.0 - 2018-03-04

#### Added

* [#186](https://github.com/acelaya/ZF-AcMailer/issues/186) Defined a MailListenerTrait that can be used when it is not possible to extend AbstractMailListener
* [#185](https://github.com/acelaya/ZF-AcMailer/issues/185) Allowed to register custom file attachment parsers
* [#194](https://github.com/acelaya/ZF-AcMailer/issues/194) Improved InvalidArgumentException::fromValidTypes message to provide more meaningful information
* [#184](https://github.com/acelaya/ZF-AcMailer/issues/184) Included infection in the build process to improve tests
* [#195](https://github.com/acelaya/ZF-AcMailer/issues/195) Included a docsify-powered documentation inside the repository in the docs folder

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* [#183](https://github.com/acelaya/ZF-AcMailer/issues/183) Dropped support for PHP <7.1

#### Fixed

* *Nothing*


## 7.0.5 - 2018-03-02

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#192](https://github.com/acelaya/ZF-AcMailer/issues/192) Fixed pre-render event so that it is properly registered when listeners are defined as services


## 7.0.4 - 2018-02-28

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#190](https://github.com/acelaya/ZF-AcMailer/issues/190) Fixed regression introduced in 7.0.3 preventing external services to be configured before rendering the email template


## 7.0.3 - 2018-02-21

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#187](https://github.com/acelaya/ZF-AcMailer/issues/187) Ensured email templates are rendered before the PRE_SEND event is triggered


## 7.0.2 - 2018-01-03

#### Added

* *Nothing*

#### Changed

* *Nothing*

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#182](https://github.com/acelaya/ZF-AcMailer/issues/182) Fixed emails not containing the result of rendering a template as their body property


## 7.0.1 - 2018-01-03

#### Added

* *Nothing*

#### Changed

* [#180](https://github.com/acelaya/ZF-AcMailer/issues/180) Increased phpstan level required in build matrix from 5 to 6

#### Deprecated

* *Nothing*

#### Removed

* *Nothing*

#### Fixed

* [#179](https://github.com/acelaya/ZF-AcMailer/issues/179) Fixed ExceptionInterface so that it extends `Throwable`
* [#174](https://github.com/acelaya/ZF-AcMailer/issues/174) Removed the `layout` param which is automatically set when using expressive and zend/view, for emails only


## 7.0.0 - 2017-12-09

#### Added

* [#153](https://github.com/acelaya/ZF-AcMailer/issues/153) Added real compatibility with Zend expressive
* [#166](https://github.com/acelaya/ZF-AcMailer/issues/166) Allowed preconfigured emails to extend among themeselves
* [#167](https://github.com/acelaya/ZF-AcMailer/issues/167) Allowed listeners cancel email sending on `PRE_SEND` when returning `false`
* [#165](https://github.com/acelaya/ZF-AcMailer/issues/165) Created console migration tool, to migrate config from v5/v6 to v7: [zf-acmailer-tooling](https://github.com/acelaya/zf-acmailer-tooling)
* [#162](https://github.com/acelaya/ZF-AcMailer/issues/162) Added migration guide, explaining how to migrate form version 6 to 7
* [#169](https://github.com/acelaya/ZF-AcMailer/issues/169) Update documentation to explain new features and configuration structure
* [#163](https://github.com/acelaya/ZF-AcMailer/issues/163) Created `.gitattributes` dile defining elements to exclude when installing package from dist

#### Changed

* [#159](https://github.com/acelaya/ZF-AcMailer/issues/159) Module completely rewritten, changing the focus, and making services truly stateless
* [#172](https://github.com/acelaya/ZF-AcMailer/issues/172) Improved quality on attachments business logic
* [#170](https://github.com/acelaya/ZF-AcMailer/issues/170) Extracted `mailviewrenderer` creation to a specific factory
* [#155](https://github.com/acelaya/ZF-AcMailer/issues/155) Used `::class` magic constant whenever possible
* [#157](https://github.com/acelaya/ZF-AcMailer/issues/157) Improved coding standards strictness by using slevomat/coding-standard

#### Deprecated

* *Nothing*

#### Removed

* [#168](https://github.com/acelaya/ZF-AcMailer/issues/168) Dropped controller plugin, forcing the MailService to be injected in controllers
* [#154](https://github.com/acelaya/ZF-AcMailer/issues/154) Dropped PHP 5 support
* [#160](https://github.com/acelaya/ZF-AcMailer/issues/160) Removed everything which was deprecated in v6
* [#161](https://github.com/acelaya/ZF-AcMailer/issues/161) Removed config migration from AcMailer 4.5 and earlier to AcMailer 5.0

#### Fixed

* [#173](https://github.com/acelaya/ZF-AcMailer/issues/173) Ensured the charset is applied to all parts of the email body
* [#171](https://github.com/acelaya/ZF-AcMailer/issues/171) Ensured proper filename is discovered when adding an attachment without providing a name


## 6.4.0

**Enhancements:**

* [144: Improve file attachment strategies](https://github.com/acelaya/ZF-AcMailer/issues/144)


## 6.3.1

**Tasks**

* [143: Add php 7.1 to build matrix and drop hhvm](https://github.com/acelaya/ZF-AcMailer/issues/143)

**Bugs:**

* [142: Events with ZF3 doesn't work](https://github.com/acelaya/ZF-AcMailer/issues/142)


## 6.3.0

**Enhancements:**

* [135: Added header encoding option](https://github.com/acelaya/ZF-AcMailer/pull/135)


## 6.2.0

**Enhancements:**

* [130: Added ConfigProvider support](https://github.com/acelaya/ZF-AcMailer/pull/130)
* [133: Added replyTo support in controller plugin](https://github.com/acelaya/ZF-AcMailer/pull/133)


## 6.1.0

**Enhancements:**

* [129: Make module installable via zend-component-installer](https://github.com/acelaya/ZF-AcMailer/pull/129)


## 6.0.0

**Enhancements:**

* [103: Drop support for the mail_options top-level configuration key](https://github.com/acelaya/ZF-AcMailer/issues/103)
* [124: Update to ZF3 components](https://github.com/acelaya/ZF-AcMailer/issues/124)

**Tasks**

* [126: Drop Support for PHP 5.5](https://github.com/acelaya/ZF-AcMailer/issues/126)


## 5.2.1

**Bugs:**

* [125: False is unsupported SSL type](https://github.com/acelaya/ZF-AcMailer/issues/125)


## 5.2.0

**Enhancements:**

* [104: Allow to define the Renderer to be used for each service](https://github.com/acelaya/ZF-AcMailer/issues/104)

**Tasks**

* [114: Drop Support for PHP 5.4](https://github.com/acelaya/ZF-AcMailer/issues/114)


## 5.1.1

**Bugs:**

* [113: The extends property is mapped when its value is null, making the MailOptions to throw an exception](https://github.com/acelaya/ZF-AcMailer/issues/113)


## 5.1.0

**Enhancements:**

* [110: ReplyTo message option is missing in configuration](https://github.com/acelaya/ZF-AcMailer/issues/110)
* [112: Document replyTo and replyToName config options](https://github.com/acelaya/ZF-AcMailer/issues/112)

**Tasks**

* [108: Remove the whole framework as a dev dependency](https://github.com/acelaya/ZF-AcMailer/issues/108)


## 5.0.1

**Bugs:**

* [105: The EVENT_MAIL_PRE_SEND event should be triggered before the files are attached to the email](https://github.com/acelaya/ZF-AcMailer/issues/105)


## 5.0.0

**Enhancements:**

* [66: Allow to register multiple mail services, each one consuming its own configuration](https://github.com/acelaya/ZF-AcMailer/issues/66)
* [77: Remove autoloader files and support only composer installation method](https://github.com/acelaya/ZF-AcMailer/issues/77)
* [67: Group related config in common groups](https://github.com/acelaya/ZF-AcMailer/issues/67)
* [82: Merge mail_adapter and mail_adapter_service configuration options into a single one, and make the factory to check if it is a service or not](https://github.com/acelaya/ZF-AcMailer/issues/82)
* [75: Allow to define charset to be used in email body when it is set via template](https://github.com/acelaya/ZF-AcMailer/issues/75)
* [78: Allow a base layout to be defined for all the emails](https://github.com/acelaya/ZF-AcMailer/issues/78)
* [84: Allow to define event managers at configuration level](https://github.com/acelaya/ZF-AcMailer/issues/84)
* [85: Improve code coverage after broken tests are fixed](https://github.com/acelaya/ZF-AcMailer/issues/85)
* [86: Add new configuration structure to the documentation](https://github.com/acelaya/ZF-AcMailer/issues/86)
* [91: Fix PHP 7 build](https://github.com/acelaya/ZF-AcMailer/issues/91)

**Tasks**

* [74: Set minimum PHP version to 5.4](https://github.com/acelaya/ZF-AcMailer/issues/74)
* [79: Change license to MIT](https://github.com/acelaya/ZF-AcMailer/issues/79)
* [69: Remove deprecated methods](https://github.com/acelaya/ZF-AcMailer/issues/69)
* [87: Mark setSubject method in MailServiceInterface as deprecated](https://github.com/acelaya/ZF-AcMailer/issues/87)
* [73: Improve code quality](https://github.com/acelaya/ZF-AcMailer/issues/73)
* [89: Add PHP 7 to travis configuration](https://github.com/acelaya/ZF-AcMailer/issues/89)
* [80: Add BC break warning between 5.0 and previous versions in README](https://github.com/acelaya/ZF-AcMailer/issues/80)
* [68: Create a changelog file and list changes in github releases too](https://github.com/acelaya/ZF-AcMailer/issues/68)
* [90: Create CLI entry point to migrate old config to new config](https://github.com/acelaya/ZF-AcMailer/issues/90)


## 4.5.1

**Bugs**:

* [96. Fix errors introduced with ZF 2.3.9 and 2.4.2](https://github.com/acelaya/ZF-AcMailer/issues/97)


## 4.5.0

**Enhancements:**

* [64: Create a controller plugin to access mail service](https://github.com/acelaya/ZF-AcMailer/issues/64)

**Bug fixes:**

* [65: Fixed UTF-8 problems when sending email with attachments](https://github.com/acelaya/ZF-AcMailer/issues/65)
* [70: Make sure multipart messages with attachments are properly working](https://github.com/acelaya/ZF-AcMailer/issues/70)

**Tasks:**

* [71: Replace usages of Zend\Mime objects with aliases by the original name with the Mime namespace](https://github.com/acelaya/ZF-AcMailer/issues/71)
* [72: Refactor code to be more coherent](https://github.com/acelaya/ZF-AcMailer/issues/72)
* [53: Improve code quality](https://github.com/acelaya/ZF-AcMailer/issues/53)
