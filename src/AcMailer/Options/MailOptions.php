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
     * Mail adapter should be one of this types
     * @var array
     */
    private $validAdapters = array(
        'Zend\Mail\Transport\Sendmail' => 'Zend\Mail\Transport\Sendmail',
        'Sendmail'                     => 'Zend\Mail\Transport\Sendmail',
        'Zend\Mail\Transport\Smtp'     => 'Zend\Mail\Transport\Smtp',
        'Smtp'                         => 'Zend\Mail\Transport\Smtp',
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
     * @var string
     */
    protected $mailAdapter = '\Zend\Mail\Transport\Sendmail';
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
     * @var string
     * @deprecated
     */
    protected $attachmentsDir = 'data/mail/attachments';
    /**
     * @var AttachmentsOptions
     */
    protected $attachments;
    
    /**
     * @return TransportInterface the $mailAdapter
     */
    public function getMailAdapter()
    {
        if (!$this->mailAdapter instanceof TransportInterface) {
            $this->mailAdapter = new $this->mailAdapter();
        }

        return $this->mailAdapter;
    }

    /**
     * @param string|\Zend\Mail\Transport\Smtp|\Zend\Mail\Transport\Sendmail $mailAdapter class name
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setMailAdapter($mailAdapter)
    {
        if (is_string($mailAdapter)) {
            $mailAdapter = ucfirst($mailAdapter);
            if (array_key_exists($mailAdapter, $this->validAdapters)) {
                $this->mailAdapter = $this->validAdapters[$mailAdapter];
            } else {
                throw new InvalidArgumentException(sprintf(
                    "Defined adapter as string is not a valid adapter. Value should be one of '%s'",
                    implode("', '", array_keys($this->validAdapters))
                ));
            }
        } elseif ($mailAdapter instanceof Sendmail || $mailAdapter instanceof Smtp) {
           $this->mailAdapter = $mailAdapter;
        } else {
           throw new InvalidArgumentException(
               "Defined adapter should be an instance of 'Zend\\Mail\\Transport\\Smtp' or 'Zend\\Mail\\Transport\\Sendmail'"
           );
        }

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
            throw new InvalidArgumentException(
                'Boolean true value for SSL is not supported. Only false can be used to disable SSL, otherwise "ssl" or "tls" values should be used.'
            );
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
     * @return string
     * @deprecated
     */
    public function getAttachmentsDir()
    {
        return $this->attachmentsDir;
    }
    /**
     * Sets attachments dir
     * @param string $attachmentsDir
     * @return \AcMailer\Options\MailOptions
     * @deprecated
     */
    public function setAttachmentsDir($attachmentsDir)
    {
        $this->attachmentsDir = $attachmentsDir;
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
        } else if ($attachments instanceof AttachmentsOptions) {
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
        return $this->attachments;
    }

}
