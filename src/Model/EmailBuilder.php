<?php
declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception;
use Zend\Stdlib\ArrayUtils;

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
        if (! isset($this->emailsConfig[$name])) {
            throw Exception\EmailNotFoundException::fromName($name);
        }

        return new Email(ArrayUtils::merge($this->emailsConfig[$name], $options));
    }
}
