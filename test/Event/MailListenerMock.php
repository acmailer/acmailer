<?php
namespace AcMailerTest\Event;

use AcMailer\Event\AbstractMailListener;
use AcMailer\Event\MailEvent;

/**
 * Class MailListenerMock
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailListenerMock extends AbstractMailListener
{
    private $onPreSendCalled    = false;
    private $onPostSendCalled   = false;
    private $onSendErrorCalled  = false;

    /**
     * Called before sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreSend(MailEvent $e)
    {
        $this->onPreSendCalled = true;
    }

    /**
     * Called after sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onPostSend(MailEvent $e)
    {
        $this->onPostSendCalled = true;
    }

    /**
     * Called if an error occurs while sending the email
     * @param MailEvent $e
     * @return mixed
     */
    public function onSendError(MailEvent $e)
    {
        $this->onSendErrorCalled = true;
    }

    /**
     * @return bool
     */
    public function isOnPreSendCalled()
    {
        return $this->onPreSendCalled;
    }
    /**
     * @return bool
     */
    public function isOnPostSendCalled()
    {
        return $this->onPostSendCalled;
    }
    /**
     * @return bool
     */
    public function isOnSendErrorCalled()
    {
        return $this->onSendErrorCalled;
    }
}
