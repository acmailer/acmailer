<?php
namespace AcMailer\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\Sendmail;
use AcMailer\Exception\InvalidArgumentException;

/**
 * Module options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptions extends AbstractOptions
{
    /**
     * Standard adapters aliasses
     * @var array
     */
    private $adapterMap = array(
        'sendmail'  => 'Zend\Mail\Transport\Sendmail',
        'smtp'      => 'Zend\Mail\Transport\Smtp',
        'null'      => 'Zend\Mail\Transport\Null',
        'file'      => 'Zend\Mail\Transport\File',
    );
    /**
     * Valid SSL values
     * @var array
     */
    private $validSsl = array(
        'ssl',
        'tls',
    );
    /**
     * Valid connection class values
     * @var array
     */
    private $validConnectionClasses = array(
        'smtp',
        'plain',
        'login',
        'crammd5',
    );
    
    /**
     * @var string|TransportInterface
     */
    protected $mailAdapter = '\Zend\Mail\Transport\Sendmail';
    /**
     * @var string|null
     */
    protected $mailAdapterService = null;
    /**
     * @var string
     */
    protected $server = 'localhost';
    /**
     * @var string
     */
    protected $from = '';
    /**
     * @var string
     */
    protected $fromName = '';
    /**
     * @var array
     */
    protected $to = array();
    /**
     * @var array
     */
    protected $cc = array();
    /**
     * @var array
     */
    protected $bcc = array();
    /**
     * @var string
     */
    protected $smtpUser = '';
    /**
     * @var string
     */
    protected $smtpPassword = '';
    /**
     * @var string|bool
     */
    protected $ssl = false;
    /**
     * @var string
     */
    protected $connectionClass = 'login';
    /**
     * @var string
     */
    protected $subject = '';
    /**
     * @var string
     */
    protected $body = '';
    /**
     * @var TemplateOptions
     */
    protected $template;
    /**
     * @var int
     */
    protected $port = 25;
    /**
     * @var AttachmentsOptions
     */
    protected $attachments;
    /**
     * @var string
     */
    protected $filePath = 'data/mail/output';
    /**
     * @var callable
     */
    protected $fileCallback = null;
    
    /**
     * @return TransportInterface the $mailAdapter
     */
    public function getMailAdapter()
    {
        if (is_string($this->mailAdapter)) {
            $this->setMailAdapter($this->mailAdapter);
        }

        return $this->mailAdapter;
    }

    /**
     * @param string|TransportInterface $mailAdapter
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setMailAdapter($mailAdapter)
    {
        if (is_string($mailAdapter)) {
            if (array_key_exists(strtolower($mailAdapter), $this->adapterMap)) {
                $mailAdapter = $this->adapterMap[strtolower($mailAdapter)];
            }
            if (!class_exists($mailAdapter)) {
                throw new InvalidArgumentException(sprintf('Provided adapter class "%s" does not exist', $mailAdapter));
            }

            $mailAdapter = new $mailAdapter();
        }
        if (!$mailAdapter instanceof TransportInterface) {
            throw new InvalidArgumentException(sprintf(
                'Provided adapter of type "%s" is not valid, expected a Zend\\Mail\\Transport\\TransportInterface',
                is_object($mailAdapter) ? get_class($mailAdapter) : gettype($mailAdapter)
            ));
        }

        $this->mailAdapter = $mailAdapter;
        return $this;
    }

    /**
     * @return string $server
     */
    public function getServer()
    {
        return $this->server;
    }
    /**
     * @param string $server
     * @return MailOptions
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @return string $from
     */
    public function getFrom()
    {
        return $this->from;
    }
    /**
     * @param string $from
     * @return MailOptions
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string $fromName
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return array $to
     */
    public function getTo()
    {
        return $this->to;
    }
    /**
     * @param array $to
     * @return MailOptions
     */
    public function setTo($to)
    {
        $this->to = (array) $to;
        return $this;
    }

    /**
     * @return array $cc
     */
    public function getCc()
    {
        return $this->cc;
    }
    /**
     * @param array $cc
     * @return MailOptions
     */
    public function setCc($cc)
    {
        $this->cc = (array) $cc;
        return $this;
    }

    /**
     * @return array $bcc
     */
    public function getBcc()
    {
        return $this->bcc;
    }
    /**
     * @param array $bcc
     * @return MailOptions
     */
    public function setBcc($bcc)
    {
        $this->bcc = (array) $bcc;
        return $this;
    }

    /**
     * @return string $smtpUser
     */
    public function getSmtpUser()
    {
        if (!isset($this->smtpUser) || $this->smtpUser == "") {
            return $this->from;
        }

        return $this->smtpUser;
    }
    /**
     * @param string $smtpUser
     * @return MailOptions
     */
    public function setSmtpUser($smtpUser)
    {
        $this->smtpUser = $smtpUser;
        return $this;
    }
    
    /**
     * @return string|boolean
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * @param string|boolean $ssl
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setSsl($ssl)
    {
        if (!is_bool($ssl) && !is_string($ssl)) {
            throw new InvalidArgumentException('SSL value should be false, "ssl" or "tls".');
        } elseif (is_bool($ssl) && $ssl !== false) {
            throw new InvalidArgumentException(sprintf(
                'Supported values are boolean false, "ssl" or "tls", %s provided',
                is_object(($ssl)) ? get_class($ssl) : gettype($ssl)
            ));
        } elseif (is_string($ssl) && !in_array($ssl, $this->validSsl)) {
            throw new InvalidArgumentException('SSL valid values are "ssl" or "tls".');
        }

        $this->ssl = $ssl;
        return $this;
    }

    /**
     * @return string $smtpPassword
     */
    public function getSmtpPassword()
    {
        return $this->smtpPassword;
    }
    /**
     * @param string $smtpPassword
     * @return MailOptions
     */
    public function setSmtpPassword($smtpPassword)
    {
        $this->smtpPassword = $smtpPassword;
        return $this;
    }

    /**
     * @return string $body
     */
    public function getBody()
    {
        return $this->body;
    }
    /**
     * @param string $body
     * @return MailOptions
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }
    /**
     * @param string $subject
     * @return MailOptions
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return int $port
     */
    public function getPort()
    {
        return (int) $this->port;
    }
    /**
     * @param int $port
     * @return MailOptions
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }
    
    /**
     * @return TemplateOptions
     */
    public function getTemplate()
    {
        if (!isset($this->template)) {
            $this->setTemplate(array());
        }

        return $this->template;
    }
    /**
     * @param array|TemplateOptions $template
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setTemplate($template)
    {
        if (is_array($template)) {
            $this->template = new TemplateOptions($template);
        } elseif ($template instanceof TemplateOptions) {
            $this->template = $template;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Template should be an array or an AcMailer\Options\TemplateOptions object. %s provided.',
                is_object($template) ? get_class($template) : gettype($template)
            ));
        }

        return $this;
    }

    /**
     * @param $connectionClass
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setConnectionClass($connectionClass)
    {
        if (!in_array($connectionClass, $this->validConnectionClasses)) {
            throw new InvalidArgumentException(sprintf(
                "Connection class should be one of '%s'. %s provided",
                implode("', '", $this->validConnectionClasses),
                $connectionClass
            ));
        }

        $this->connectionClass = $connectionClass;
        return $this;
    }
    /**
     * @return string
     */
    public function getConnectionClass()
    {
        return $this->connectionClass;
    }

    /**
     * @param array|AttachmentsOptions $attachments
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setAttachments($attachments)
    {
        if (is_array($attachments)) {
            $this->attachments = new AttachmentsOptions($attachments);
        } elseif ($attachments instanceof AttachmentsOptions) {
            $this->attachments = $attachments;
        } else {
            throw new InvalidArgumentException(sprintf(
                "Attachments should be an array or an AcMailer\\Options\\AttachmentsOptions, %s provided",
                is_object($attachments) ? get_class($attachments) : gettype($attachments)
            ));
        }

        return $this;
    }
    /**
     * @return AttachmentsOptions
     */
    public function getAttachments()
    {
        if (!isset($this->attachments)) {
            $this->setAttachments(array());
        }

        return $this->attachments;
    }

    /**
     * @param $mailAdapterService
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setMailAdapterService($mailAdapterService)
    {
        if (!is_null($mailAdapterService) && !is_string($mailAdapterService)) {
            throw new InvalidArgumentException(sprintf(
                'Provided value of type "%s" is not valid. Expected "string" or "null"',
                is_object($mailAdapterService) ? get_class($mailAdapterService): gettype($mailAdapterService)
            ));
        }

        $this->mailAdapterService = $mailAdapterService;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMailAdapterService()
    {
        return $this->mailAdapterService;
    }

    /**
     * @param callable $fileCallback
     * @return $this;
     */
    public function setFileCallback($fileCallback)
    {
        $this->fileCallback = $fileCallback;
        return $this;
    }

    /**
     * @return callable
     */
    public function getFileCallback()
    {
        return $this->fileCallback;
    }

    /**
     * @param $filePath
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setFilePath($filePath)
    {
        if (!is_string($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'Provided value of type "%s" is not valid. Expected "string"',
                is_object($filePath) ? get_class($filePath): gettype($filePath)
            ));
        }

        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
