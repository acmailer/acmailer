<?php
namespace AcMailer\Result;

/**
 * 
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface ResultInterface
{
    
    /**
     * Returns error message when an error occurs
     * @return string
     */
    public function getMessage();
    
    /**
     * Tells if the MailService that produced this result was properly sent
     * @return boolean
     */
    public function isValid();
    
}