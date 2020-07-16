<?php

declare(strict_types=1);

namespace AcMailer\Event;

use AcMailer\Model\Email;
use Laminas\EventManager\Event;

abstract class WithoutResultAbstractEvent extends Event
{
    private Email $email;

    public function __construct(Email $email)
    {
        parent::__construct(static::class);
        $this->email = $email;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
