<?php

declare(strict_types=1);

namespace AcMailer\Result;

use AcMailer\Model\Email;
use Throwable;

class MailResult implements ResultInterface
{
    private bool $valid;
    private Email $email;
    private ?Throwable $throwable;

    public function __construct(Email $email, bool $valid = true, ?Throwable $throwable = null)
    {
        $this->email = $email;
        $this->valid = $valid;
        $this->throwable = $throwable;
    }

    /**
     * Returns the email that was tried to be sent
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * Tells if the MailService that produced this result was properly sent
     *
     * @deprecated
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Tells if this Result has an exception. Usually only non-valid result should wrap an exception
     */
    public function hasThrowable(): bool
    {
        return $this->throwable !== null;
    }

    /**
     * Returns the exception wrapped by this Result if any, or null otherwise
     */
    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * Tells if the email sending was cancelled, usually by a preSend listener
     *
     * @deprecated
     */
    public function isCancelled(): bool
    {
        return ! $this->isValid() && ! $this->hasThrowable();
    }
}
