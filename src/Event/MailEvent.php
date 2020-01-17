<?php

declare(strict_types=1);

namespace AcMailer\Event;

use AcMailer\Model\Email;
use AcMailer\Result\ResultAwareInterface;
use AcMailer\Result\ResultInterface;
use Laminas\EventManager\Event;

class MailEvent extends Event implements ResultAwareInterface
{
    public const EVENT_MAIL_PRE_RENDER = 'event.mail.pre.render';
    public const EVENT_MAIL_PRE_SEND = 'event.mail.pre.send';
    public const EVENT_MAIL_POST_SEND = 'event.mail.post.send';
    public const EVENT_MAIL_SEND_ERROR = 'event.mail.send.error';

    protected ResultInterface $result;
    private Email $email;

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
    public function setResult(ResultInterface $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        return $this->result;
    }

    public static function getEventNames(): array
    {
        return [
            'onPreRender' => self::EVENT_MAIL_PRE_RENDER,
            'onPreSend' => self::EVENT_MAIL_PRE_SEND,
            'onPostSend' => self::EVENT_MAIL_POST_SEND,
            'onSendError' => self::EVENT_MAIL_SEND_ERROR,
        ];
    }
}
