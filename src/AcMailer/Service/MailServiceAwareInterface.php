<?php
namespace AcMailer\Service;

/**
 * Interface MailServiceAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceAwareInterface {

	/**
	 * @param MailServiceInterface $mailService
	 */
	public function setMailService(MailServiceInterface $mailService);

	/**
	 * @return MailServiceInterface
	 */
	public function getMailService();

} 