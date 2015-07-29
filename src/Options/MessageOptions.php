<?php
namespace AcMailer\Options;

use AcMailer\Exception\InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

/**
 * Class MessageOptions
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MessageOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $from = '';
    /**
     * @var string
     */
    protected $fromName = '';
    /**
     * @var string
     */
    protected $replyTo = '';
    /**
     * @var string
     */
    protected $replyToName = '';
    /**
     * @var array
     */
    protected $to = [];
    /**
     * @var array
     */
    protected $cc = [];
    /**
     * @var array
     */
    protected $bcc = [];
    /**
     * @var string
     */
    protected $subject = '';
    /**
     * @var BodyOptions
     */
    protected $body;
    /**
     * @var AttachmentsOptions
     */
    protected $attachments;

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToName()
    {
        return $this->replyToName;
    }

    /**
     * @param string $replyToName
     * @return $this
     */
    public function setReplyToName($replyToName)
    {
        $this->replyToName = $replyToName;

        return $this;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param array|string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (array) $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param array|string $cc
     * @return $this
     */
    public function setCc($cc)
    {
        $this->cc = (array) $cc;
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param string|array $bcc
     * @return $this
     */
    public function setBcc($bcc)
    {
        $this->bcc = (array) $bcc;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return BodyOptions
     */
    public function getBody()
    {
        if (! isset($this->body)) {
            $this->setBody([]);
        }

        return $this->body;
    }

    /**
     * @param BodyOptions|array $body
     * @return $this
     */
    public function setBody($body)
    {
        if (is_array($body)) {
            $this->body = new BodyOptions($body);
        } elseif ($body instanceof BodyOptions) {
            $this->body = $body;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Body should be an array or an AcMailer\\Options\\BodyOptions, %s provided',
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        return $this;
    }

    /**
     * @return AttachmentsOptions
     */
    public function getAttachments()
    {
        if (! isset($this->attachments)) {
            $this->setAttachments([]);
        }

        return $this->attachments;
    }

    /**
     * @param AttachmentsOptions|array $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        if (is_array($attachments)) {
            $this->attachments = new AttachmentsOptions($attachments);
        } elseif ($attachments instanceof AttachmentsOptions) {
            $this->attachments = $attachments;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Attachments should be an array or an AcMailer\\Options\\AttachmentsOptions, %s provided',
                is_object($attachments) ? get_class($attachments) : gettype($attachments)
            ));
        }

        return $this;
    }
}
