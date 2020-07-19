<?php

declare(strict_types=1);

namespace AcMailer\Event;

use AcMailer\Model\Email;

abstract class WithoutResultAbstractEvent
{
    private Email $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
