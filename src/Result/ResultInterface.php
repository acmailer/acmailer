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
     * @return bool
     */
    public function isValid();

    /**
     * Tells if this Result has an exception. Usually only non-valid result should wrap an exception
     * @return bool
     */
    public function hasException();

    /**
     * Returns the exception wraped by this Result
     * @return \Exception
     */
    public function getException();
}
