<?php

declare(strict_types=1);

namespace AcMailer\Result;

use AcMailer\Model\Email;
use Throwable;

interface ResultInterface
{
    /**
     * Returns the email that was tried to be sent
     */
    public function getEmail(): Email;

    /**
     * Tells if the email was properly sent
     */
    public function isValid(): bool;

    /**
     * Tells if the email sending was cancelled, usually by a preSend listener
     */
    public function isCancelled(): bool;

    /**
     * Tells if this Result has an exception. Usually only non-valid result should wrap an exception
     */
    public function hasException(): bool;

    /**
     * Returns the exception wrapped by this Result if any, or null otherwise
     */
    public function getException(): ?Throwable;
}
