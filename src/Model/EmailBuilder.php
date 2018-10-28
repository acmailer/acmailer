<?php
declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception;
use Zend\Stdlib\ArrayUtils;
use function in_array;

class EmailBuilder implements EmailBuilderInterface
{
    /**
     * @var array
     */
    private $emailsConfig;

    public function __construct(array $emailsConfig)
    {
        $this->emailsConfig = $emailsConfig;
        // Always make the identifier Email be valid, in order to build any kind of anonymous email
        $this->emailsConfig[Email::class] = [];
    }

    /**
     * @param string $name
     * @param array $options
     * @return Email
     * @throws Exception\EmailNotFoundException
     * @throws Exception\InvalidArgumentException
     */
    public function build(string $name, array $options = []): Email
    {
        return new Email($this->buildOptions($name, $options));
    }

    private function buildOptions(string $name, array $options, array &$alreadyExtendedEmails = []): array
    {
        if (! isset($this->emailsConfig[$name])) {
            throw Exception\EmailNotFoundException::fromName($name);
        }

        // Recursively extend emails
        $options = ArrayUtils::merge($this->emailsConfig[$name], $options);
        if (! isset($options['extends'])) {
            return $options;
        }

        // Get the email from which to extend, and ensure it has not been processed yet, to prevent an infinite loop
        $emailToExtend = $options['extends'];
        if (in_array($emailToExtend, $alreadyExtendedEmails, true)) {
            throw new Exception\InvalidArgumentException(
                'It wasn\'t possible to create an email due to circular inheritance. Review "extends".'
            );
        }
        $alreadyExtendedEmails[] = $emailToExtend;
        unset($options['extends']);

        return ArrayUtils::merge($this->buildOptions($emailToExtend, [], $alreadyExtendedEmails), $options);
    }
}
