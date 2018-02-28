<?php
declare(strict_types=1);

namespace AcMailer\Event;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class AbstractMailListener
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
abstract class AbstractMailListener extends AbstractListenerAggregate implements MailListenerInterface
{
    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_PRE_RENDER, [$this, 'onPreRender'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this, 'onPreSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this, 'onPostSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this, 'onSendError'], $priority);
    }

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
