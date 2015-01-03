<?php
namespace AcMailer\Options;

/**
 * Interface MailOptionsAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailOptionsAwareInterface
{
    /**
     * @return MailOptions
     */
    public function getMailOptions();
    
    /**
     * @param MailOptions $options
     */
    public function setMailOptions(MailOptions $options);
}
