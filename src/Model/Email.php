<?php
declare(strict_types=1);

namespace AcMailer\Model;

use Zend\Mime\Part;
use Zend\Stdlib\AbstractOptions;

final class Email extends AbstractOptions
{
    /**
     * @var string
     */
    private $from = '';
    /**
     * @var string
     */
    private $fromName = '';
    /**
     * @var string
     */
    private $replyTo = '';
    /**
     * @var string
     */
    private $replyToName = '';
    /**
     * @var array
     */
    private $to = [];
    /**
     * @var array
     */
    private $cc = [];
    /**
     * @var array
     */
    private $bcc = [];
    /**
     * @var string
     */
    private $encoding = '';
    /**
     * @var string
     */
    private $subject = '';
    /**
     * @var string
     */
    private $body;
    /**
     * @var string|null
     */
    private $template;
    /**
     * @var array
     */
    private $templateParams = [];
    /**
     * @var array
     */
    private $attachments;

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return $this|self
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return $this|self
     */
    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     * @return $this|self
     */
    public function setReplyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToName(): string
    {
        return $this->replyToName;
    }

    /**
     * @param string $replyToName
     * @return $this|self
     */
    public function setReplyToName(string $replyToName): self
    {
        $this->replyToName = $replyToName;
        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param array $to
     * @return $this|self
     */
    public function setTo(array $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param array $cc
     * @return $this|self
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @param array $bcc
     * @return $this|self
     */
    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return $this|self
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this|self
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this|self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string|resource|array|Part $file
     * @param string|null $filename
     * @return $this
     */
    public function addAttachment($file, $filename = null): self
    {
        if ($filename !== null) {
            $this->attachments[$filename] = $file;
        } else {
            $this->attachments[] = $file;
        }
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function addAttachments(array $files): self
    {
        return $this->setAttachments(\array_merge($this->attachments, $files));
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setAttachments(array $files): self
    {
        $this->attachments = $files;
        return $this;
    }

    /**
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @return string|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     * @return $this|self
     */
    public function setTemplate(string $template = null): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return $this->template !== null;
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }

    /**
     * @param array $templateParams
     * @return $this|self
     */
    public function setTemplateParams(array $templateParams): self
    {
        $this->templateParams = $templateParams;
        return $this;
    }
}
