<?php

declare(strict_types=1);

namespace AcMailer\Event;

interface MailListenerInterface
{
    /**
     * @return mixed
     */
    public function onPreRender(PreRenderEvent $e);

    /**
     * @return mixed
     */
    public function onPreSend(PreSendEvent $e);

    /**
     * @return mixed
     */
    public function onPostSend(PostSendEvent $e);

    /**
     * @return mixed
     */
    public function onSendError(SendErrorEvent $e);
}
