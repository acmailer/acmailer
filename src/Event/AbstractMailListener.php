<?php

declare(strict_types=1);

namespace AcMailer\Event;

abstract class AbstractMailListener implements MailListenerInterface
{
    use MailListenerTrait;

    /**
     * Called before rendering the email, in case it is composed by a template
     *
     * @return mixed
     */
    public function onPreRender(PreRenderEvent $e)
    {
        // TODO: Implement onPreRender() method.
    }

    /**
     * Called before sending the email, but after rendering it
     *
     * @return mixed
     */
    public function onPreSend(PreSendEvent $e)
    {
        // TODO: Implement onPreSend() method.
    }

    /**
     * Called after sending the email
     *
     * @return mixed
     */
    public function onPostSend(PostSendEvent $e)
    {
        // TODO: Implement onPostSend() method.
    }

    /**
     * Called if an error occurs while sending the email
     *
     * @return mixed
     */
    public function onSendError(SendErrorEvent $e)
    {
        // TODO: Implement onSendError() method.
    }
}
