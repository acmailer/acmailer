<?php
namespace AcMailer\Event;

/**
 * Interface MailListenerAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailListenerAwareInterface
{
    /**
     * Attaches a new MailListenerInterface
     * @param MailListenerInterface $mailListener
     * @param int $priority
     * @return mixed
     */
    public function attachMailListener(MailListenerInterface $mailListener, $priority = 1);
}
