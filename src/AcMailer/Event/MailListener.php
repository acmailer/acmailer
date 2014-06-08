<?php
namespace AcMailer\Event;

/**
 * Interface MailListener
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailListener
{

	/**
	 * Called before sending the email
	 * @param MailEvent $e
	 * @return mixed
	 */
	public function onPreSend(MailEvent $e);

	/**
	 * Called after sending the email
	 * @param MailEvent $e
	 * @return mixed
	 */
	public function onPostSend(MailEvent $e);

	/**
	 * Called if an error occurs while sending the email
	 * @param MailEvent $e
	 * @return mixed
	 */
	public function onSendError(MailEvent $e);

} 