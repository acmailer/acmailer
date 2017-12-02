<?php
namespace AcMailer\Result;

use AcMailer\Model\Email;

/**
 * Object returned by send method in MailService
 * @see \AcMailer\Service\MailServiceInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailResult implements ResultInterface
{
    /**
     * @var bool
     */
    private $valid;
    /**
     * @var Email
     */
    private $email;
    /**
     * @var \Throwable
     */
    private $exception;

    public function __construct(Email $email, bool $valid = true, \Throwable $exception = null)
    {
        $this->email = $email;
        $this->valid = $valid;
        $this->exception = $exception;
    }

    /**
     * Returns the email that was tried to be sent
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * Tells if the MailService that produced this result was properly sent
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Tells if this Result has an exception. Usually only non-valid result should wrap an exception
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception !== null;
    }

    /**
     * Returns the exception wrapped by this Result if any, or null otherwise
     * @return \Throwable|null
     */
    public function getException()
    {
        return $this->exception;
    }
}
