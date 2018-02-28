<?php
declare(strict_types=1);

namespace AcMailer\Event;

use AcMailer\Model\Email;
use AcMailer\Result\ResultAwareInterface;
use AcMailer\Result\ResultInterface;
use Zend\EventManager\Event;

/**
 * Encapsulation of a Mail event
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailEvent extends Event implements ResultAwareInterface
{
    const EVENT_MAIL_PRE_RENDER   = 'event.mail.pre.render';
    const EVENT_MAIL_PRE_SEND   = 'event.mail.pre.send';
    const EVENT_MAIL_POST_SEND  = 'event.mail.post.send';
    const EVENT_MAIL_SEND_ERROR = 'event.mail.send.error';

    /**
     * @var ResultInterface
     */
    protected $result;
    /**
     * @var Email
     */
    private $email;

    public function __construct(Email $email, $name = self::EVENT_MAIL_PRE_SEND)
    {
        parent::__construct($name);
        $this->email = $email;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
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
     * @return ResultInterface|null
     */
    public function getResult()
    {
        return $this->result;
    }
}
