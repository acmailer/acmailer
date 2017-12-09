<?php
declare(strict_types=1);

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
     * @return void
     */
    public function attachMailListener(MailListenerInterface $mailListener, $priority = 1);

    /**
     * Detaches provided MailListener
     * @param MailListenerInterface $mailListener
     * @return void
     */
    public function detachMailListener(MailListenerInterface $mailListener);
}
