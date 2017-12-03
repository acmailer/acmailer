<?php
namespace AcMailer\Result;

use AcMailer\Model\Email;

/**
 *
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface ResultInterface
{
    /**
     * Returns the email that was tried to be sent
     * @return Email
     */
    public function getEmail(): Email;
    
    /**
     * Tells if the email was properly sent
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Tells if the email sending was cancelled, usually by a preSend listener
     * @return bool
     */
    public function isCancelled(): bool;

    /**
     * Tells if this Result has an exception. Usually only non-valid result should wrap an exception
     * @return bool
     */
    public function hasException(): bool;

    /**
     * Returns the exception wrapped by this Result if any, or null otherwise
     * @return \Throwable|null
     */
    public function getException();
}
