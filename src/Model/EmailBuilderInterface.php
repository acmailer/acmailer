<?php

declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception;

interface EmailBuilderInterface
{
    /**
     * @param array $options
     * @throws Exception\EmailNotFoundException
     * @throws Exception\InvalidArgumentException
     */
    public function build(string $name, array $options = []): Email;
}
