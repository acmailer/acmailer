<?php
declare(strict_types=1);

namespace AcMailer\Model;

use AcMailer\Exception\InvalidArgumentException;
use Zend\Mime\Message;
use Zend\Mime\Part;
use Zend\Stdlib\AbstractOptions;

final class Email extends AbstractOptions
{
    public const DEFAULT_CHARSET = 'utf-8';

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
     * @var string|Part|Message
     */
    private $body = '';
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
    private $attachments = [];
    /**
     * @var array
     */
    private $attachmentsDir = [];
    /**
     * @var string
     */
    private $charset = self::DEFAULT_CHARSET;

    public function __construct($options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

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

    public function addTo(string $to): self
    {
        $this->to[] = $to;
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

    public function addCc(string $cc): self
    {
        $this->cc[] = $cc;
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

    public function addBcc(string $bcc): self
    {
        $this->bcc[] = $bcc;
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
     * @return string|Part|Message
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string|Part|Message $body
     * @return $this|self
     * @throws InvalidArgumentException
     */
    public function setBody($body): self
    {
        if (! \is_string($body) && ! $body instanceof Part && ! $body instanceof Message) {
            throw InvalidArgumentException::fromValidTypes(['string', Part::class, Message::class], $body);
        }

        $this->body = $body;
        return $this;
    }

    /**
     * @param string|resource|array|Part $file
     * @param string|null $filename
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addAttachment($file, string $filename = null): self
    {
        if (! \is_string($file) && ! \is_array($file) && ! \is_resource($file) && ! $file instanceof Part) {
            throw InvalidArgumentException::fromValidTypes(['string', 'array', 'resource', Part::class], $file);
        }

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
     * @throws InvalidArgumentException
     */
    public function addAttachments(array $files): self
    {
        foreach ($files as $key => $file) {
            $this->addAttachment($file, \is_string($key) ? $key : null);
        }

        return $this;
    }

    /**
     * @param array $files
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setAttachments(array $files): self
    {
        $this->attachments = [];
        $this->addAttachments($files);

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
     * @return array
     */
    public function getAttachmentsDir(): array
    {
        return $this->attachmentsDir;
    }

    /**
     * @param array $attachmentsDir
     * @return $this|self
     */
    public function setAttachmentsDir(array $attachmentsDir): self
    {
        $this->attachmentsDir = $attachmentsDir;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return ! empty($this->attachments) || ! empty($this->attachmentsDir);
    }

    /**
     * Processes the attachments dir and merges the result with the attachments array, then returns the result
     *
     * @return array
     */
    public function getComputedAttachments(): array
    {
        if (! $this->hasAttachments()) {
            return [];
        }
        $attachments = $this->getAttachments();

        // Process the attachments dir if any, and include the files in that folder
        $dir = $this->getAttachmentsDir();
        $path = $dir['path'] ?? null;
        $recursive = (bool) ($dir['recursive'] ?? false);

        if (\is_string($path) && \is_dir($path)) {
            $files = $recursive ? new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) : new \DirectoryIterator($path);

            /* @var \SplFileInfo $fileInfo */
            foreach ($files as $fileInfo) {
                if ($fileInfo->isDir()) {
                    continue;
                }
                $attachments[] = $fileInfo->getPathname();
            }
        }

        return $attachments;
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

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this|self
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }
}
