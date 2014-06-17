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
     * Attaches a new MailListener
     * @param MailListener $mailListener
     * @param int $priority
     * @return mixed
     */
    public function attachMailListener(MailListener $mailListener, $priority = 1);
}
