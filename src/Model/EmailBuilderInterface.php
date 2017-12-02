<?php
declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception\EmailNotFoundException;

interface EmailBuilderInterface
{
    /**
     * @param string $name
     * @param array $options
     * @return Email
     * @throws EmailNotFoundException
     */
    public function build(string $name, array $options = []): Email;
}
