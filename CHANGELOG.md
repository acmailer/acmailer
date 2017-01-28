## CHANGELOG

### 6.4.0

**Enhancements:**

* [144: Improve file attachment strategies](https://github.com/acelaya/ZF2-AcMailer/issues/144)

### 6.3.1

**Tasks**

* [143: Add php 7.1 to build matrix and drop hhvm](https://github.com/acelaya/ZF2-AcMailer/issues/143)

**Bugs:**

* [142: Events with ZF3 doesn't work](https://github.com/acelaya/ZF2-AcMailer/issues/142)

### 6.3.0

**Enhancements:**

* [135: Added header encoding option](https://github.com/acelaya/ZF2-AcMailer/pull/135)

### 6.2.0

**Enhancements:**

* [130: Added ConfigProvider support](https://github.com/acelaya/ZF2-AcMailer/pull/130)
* [133: Added replyTo support in controller plugin](https://github.com/acelaya/ZF2-AcMailer/pull/133)

### 6.1.0

**Enhancements:**

* [129: Make module installable via zend-component-installer](https://github.com/acelaya/ZF2-AcMailer/pull/129)

### 6.0.0

**Enhancements:**

* [103: Drop support for the mail_options top-level configuration key](https://github.com/acelaya/ZF2-AcMailer/issues/103)
* [124: Update to ZF3 components](https://github.com/acelaya/ZF2-AcMailer/issues/124)

**Tasks**

* [126: Drop Support for PHP 5.5](https://github.com/acelaya/ZF2-AcMailer/issues/126)

### 5.2.1

**Bugs:**

* [125: False is unsupported SSL type](https://github.com/acelaya/ZF2-AcMailer/issues/125)

### 5.2.0

**Enhancements:**

* [104: Allow to define the Renderer to be used for each service](https://github.com/acelaya/ZF2-AcMailer/issues/104)

**Tasks**

* [114: Drop Support for PHP 5.4](https://github.com/acelaya/ZF2-AcMailer/issues/114)

### 5.1.1

**Bugs:**

* [113: The extends property is mapped when its value is null, making the MailOptions to throw an exception](https://github.com/acelaya/ZF2-AcMailer/issues/113)

### 5.1.0

**Enhancements:**

* [110: ReplyTo message option is missing in configuration](https://github.com/acelaya/ZF2-AcMailer/issues/110)
* [112: Document replyTo and replyToName config options](https://github.com/acelaya/ZF2-AcMailer/issues/112)

**Tasks**

* [108: Remove the whole framework as a dev dependency](https://github.com/acelaya/ZF2-AcMailer/issues/108)

### 5.0.1

**Bugs:**

* [105: The EVENT_MAIL_PRE_SEND event should be triggered before the files are attached to the email](https://github.com/acelaya/ZF2-AcMailer/issues/105)

### 5.0.0

**Enhancements:**

* [66: Allow to register multiple mail services, each one consuming its own configuration](https://github.com/acelaya/ZF2-AcMailer/issues/66)
* [77: Remove autoloader files and support only composer installation method](https://github.com/acelaya/ZF2-AcMailer/issues/77)
* [67: Group related config in common groups](https://github.com/acelaya/ZF2-AcMailer/issues/67)
* [82: Merge mail_adapter and mail_adapter_service configuration options into a single one, and make the factory to check if it is a service or not](https://github.com/acelaya/ZF2-AcMailer/issues/82)
* [75: Allow to define charset to be used in email body when it is set via template](https://github.com/acelaya/ZF2-AcMailer/issues/75)
* [78: Allow a base layout to be defined for all the emails](https://github.com/acelaya/ZF2-AcMailer/issues/78)
* [84: Allow to define event managers at configuration level](https://github.com/acelaya/ZF2-AcMailer/issues/84)
* [85: Improve code coverage after broken tests are fixed](https://github.com/acelaya/ZF2-AcMailer/issues/85)
* [86: Add new configuration structure to the documentation](https://github.com/acelaya/ZF2-AcMailer/issues/86)
* [91: Fix PHP 7 build](https://github.com/acelaya/ZF2-AcMailer/issues/91)

**Tasks**

* [74: Set minimum PHP version to 5.4](https://github.com/acelaya/ZF2-AcMailer/issues/74)
* [79: Change license to MIT](https://github.com/acelaya/ZF2-AcMailer/issues/79)
* [69: Remove deprecated methods](https://github.com/acelaya/ZF2-AcMailer/issues/69)
* [87: Mark setSubject method in MailServiceInterface as deprecated](https://github.com/acelaya/ZF2-AcMailer/issues/87)
* [73: Improve code quality](https://github.com/acelaya/ZF2-AcMailer/issues/73)
* [89: Add PHP 7 to travis configuration](https://github.com/acelaya/ZF2-AcMailer/issues/89)
* [80: Add BC break warning between 5.0 and previous versions in README](https://github.com/acelaya/ZF2-AcMailer/issues/80)
* [68: Create a changelog file and list changes in github releases too](https://github.com/acelaya/ZF2-AcMailer/issues/68)
* [90: Create CLI entry point to migrate old config to new config](https://github.com/acelaya/ZF2-AcMailer/issues/90)

### 4.5.1

**Bugs**:

* [96. Fix errors introduced with ZF 2.3.9 and 2.4.2](https://github.com/acelaya/ZF2-AcMailer/issues/97)

### 4.5.0

**Enhancements:**

* [64: Create a controller plugin to access mail service](https://github.com/acelaya/ZF2-AcMailer/issues/64)

**Bug fixes:**

* [65: Fixed UTF-8 problems when sending email with attachments](https://github.com/acelaya/ZF2-AcMailer/issues/65)
* [70: Make sure multipart messages with attachments are properly working](https://github.com/acelaya/ZF2-AcMailer/issues/70)

**Tasks:**

* [71: Replace usages of Zend\Mime objects with aliases by the original name with the Mime namespace](https://github.com/acelaya/ZF2-AcMailer/issues/71)
* [72: Refactor code to be more coherent](https://github.com/acelaya/ZF2-AcMailer/issues/72)
* [53: Improve code quality](https://github.com/acelaya/ZF2-AcMailer/issues/53)
