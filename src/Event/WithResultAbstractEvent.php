<?php

declare(strict_types=1);

namespace AcMailer\Event;

use AcMailer\Model\Email;
use AcMailer\Result\ResultInterface;

abstract class WithResultAbstractEvent extends WithoutResultAbstractEvent
{
    private ResultInterface $result;

    public function __construct(Email $email, ResultInterface $result)
    {
        parent::__construct($email);
        $this->result = $result;
    }

    public function getResult(): ResultInterface
    {
        return $this->result;
    }
}
