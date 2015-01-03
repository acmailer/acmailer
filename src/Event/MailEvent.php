<?php
namespace AcMailer\Event;

use AcMailer\Result\ResultAwareInterface;
use AcMailer\Result\ResultInterface;
use AcMailer\Service\MailServiceInterface;
use Zend\EventManager\Event;

/**
 * Encapsulation of a Mail event
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailEvent extends Event implements ResultAwareInterface
{
    const EVENT_MAIL_PRE_SEND   = 'event.mail.pre.send';
    const EVENT_MAIL_POST_SEND  = 'event.mail.post.send';
    const EVENT_MAIL_SEND_ERROR = 'event.mail.send.error';

    /**
     * @var MailServiceInterface
     */
    protected $mailService;
    /**
     * @var ResultInterface
     */
    protected $result;

    public function __construct(MailServiceInterface $mailService, $name = self::EVENT_MAIL_PRE_SEND)
    {
        parent::__construct($name);
        $this->mailService = $mailService;
    }

    /**
     * @param $mailService
     * @return $this
     */
    public function setMailService($mailService)
    {
        $this->mailService = $mailService;
        return $this;
    }
    /**
     * @return \AcMailer\Service\MailServiceInterface
     */
    public function getMailService()
    {
        return $this->mailService;
    }

    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function setResult(ResultInterface $result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return ResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }
}
