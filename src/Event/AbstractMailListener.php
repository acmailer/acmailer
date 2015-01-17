<?php
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
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this, 'onPreSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this, 'onPostSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this, 'onSendError'], $priority);
    }
}
