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
     * @return $this
     */
    public function attachMailListener(MailListenerInterface $mailListener, $priority = 1);

    /**
     * Detaches provided MailListener
     * @param MailListenerInterface $mailListener
     * @return $this
     */
    public function detachMailListener(MailListenerInterface $mailListener);
}
