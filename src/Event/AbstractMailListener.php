<?php

declare(strict_types=1);

namespace AcMailer\Event;

abstract class AbstractMailListener implements MailListenerInterface
{
    use MailListenerTrait;

    /**
     * Called before rendering the email, in case it is composed by a template
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreRender(MailEvent $e)
    {
        // TODO: Implement onPreRender() method.
    }

    /**
     * Called before sending the email, but after rendering it
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreSend(MailEvent $e)
    {
        // TODO: Implement onPreSend() method.
    }

    /**
     * Called after sending the email
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPostSend(MailEvent $e)
    {
        // TODO: Implement onPostSend() method.
    }

    /**
     * Called if an error occurs while sending the email
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onSendError(MailEvent $e)
    {
        // TODO: Implement onSendError() method.
    }
}
