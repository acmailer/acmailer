<?php
namespace AcMailer\Service;

use AcMailer\Exception;
use AcMailer\Model\Email;
use AcMailer\Result\ResultInterface;

/**
 * Provides methods to be implemented by a valid MailService
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceInterface
{
    /** @deprecated Use Email::DEFAULT_CHARSET instead */
    const DEFAULT_CHARSET = Email::DEFAULT_CHARSET;

    /**
     * Tries to send the message, returning a MailResult object
     * @param string|array|Email $email
     * @param array $options
     * @return ResultInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\EmailNotFoundException
     * @throws Exception\MailException
     */
    public function send($email, array $options = []): ResultInterface;
}
