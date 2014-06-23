<?php
namespace AcMailer\Event;

/**
 * Interface MailListenerInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailListenerInterface
{
    /**
     * Called before sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreSend(MailEvent $e);

    /**
     * Called after sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onPostSend(MailEvent $e);

    /**
     * Called if an error occurs while sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onSendError(MailEvent $e);
}
