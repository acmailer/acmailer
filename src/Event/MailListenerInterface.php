<?php

declare(strict_types=1);

namespace AcMailer\Event;

use Laminas\EventManager\ListenerAggregateInterface;

interface MailListenerInterface extends ListenerAggregateInterface
{
    public const EVENT_METHOD_MAP = [
        PreRenderEvent::class => 'onPreRender',
        PreSendEvent::class => 'onPreSend',
        PostSendEvent::class => 'onPostSend',
        SendErrorEvent::class => 'onSendError',
    ];

    /**
     * Called before rendering the email, in case it is composed by a template
     *
     * @return mixed
     */
    public function onPreRender(PreRenderEvent $e);

    /**
     * Called before sending the email, but after rendering it
     *
     * @return mixed
     */
    public function onPreSend(PreSendEvent $e);

    /**
     * Called after sending the email
     *
     * @return mixed
     */
    public function onPostSend(PostSendEvent $e);

    /**
     * Called if an error occurs while sending the email
     *
     * @return mixed
     */
    public function onSendError(SendErrorEvent $e);
}
