<?php

declare(strict_types=1);

namespace AcMailer\Event;

use Laminas\EventManager\ListenerAggregateInterface;

interface MailListenerInterface extends ListenerAggregateInterface
{
    /**
     * Called before rendering the email, in case it is composed by a template
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreRender(MailEvent $e);

    /**
     * Called before sending the email, but after rendering it
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreSend(MailEvent $e);

    /**
     * Called after sending the email
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onPostSend(MailEvent $e);

    /**
     * Called if an error occurs while sending the email
     *
     * @param MailEvent $e
     * @return mixed
     */
    public function onSendError(MailEvent $e);
}
