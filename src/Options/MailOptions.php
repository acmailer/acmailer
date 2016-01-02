<?php
namespace AcMailer\Options;

use AcMailer\Exception\InvalidArgumentException;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\TransportInterface;
use Zend\Stdlib\AbstractOptions;
use Zend\View\Renderer\RendererInterface;

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
    private $adapterMap = [
        'sendmail'  => ['\Zend\Mail\Transport\Sendmail'],
        'smtp'      => ['\Zend\Mail\Transport\Smtp'],
        'in_memory' => ['\Zend\Mail\Transport\InMemory', '\Zend\Mail\Transport\Null'],
        'file'      => ['\Zend\Mail\Transport\File'],
        'null'      => ['\Zend\Mail\Transport\InMemory', '\Zend\Mail\Transport\Null'],
    ];
    
    /**
     * @var TransportInterface|string
     */
    private $mailAdapter = '\Zend\Mail\Transport\Sendmail';
    /**
     * @var MessageOptions;
     */
    private $messageOptions;
    /**
     * @var SmtpOptions;
     */
    private $smtpOptions;
    /**
     * @var FileOptions
     */
    private $fileOptions;
    /**
     * @var array
     */
    private $mailListeners = [];
    /**
     * @var string
     */
    private $renderer = 'mailviewrenderer';
    
    /**
     * @return TransportInterface|string
     */
    public function getMailAdapter()
    {
        return $this->mailAdapter;
    }

    /**
     * @param string|TransportInterface $mailAdapter
     * @return $this
     */
    public function setMailAdapter($mailAdapter)
    {
        // Map adapter aliases to the real class name
        if (is_string($mailAdapter) && array_key_exists(strtolower($mailAdapter), $this->adapterMap)) {
            $mailAdapter = $this->adapterMap[strtolower($mailAdapter)];
            foreach ($mailAdapter as $class) {
                if (class_exists($class)) {
                    $mailAdapter = $class;
                    break;
                }
            }
        }

        $this->mailAdapter = $mailAdapter;
        return $this;
    }

    /**
     * Alias for method getMailAdapter
     * @return string|TransportInterface
     */
    public function getTransport()
    {
        return $this->getMailAdapter();
    }

    /**
     * Alias for method setMailAdapter
     * @param string|TransportInterface $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        return $this->setMailAdapter($transport);
    }

    /**
     * @return MessageOptions
     */
    public function getMessageOptions()
    {
        if (! isset($this->messageOptions)) {
            $this->setMessageOptions([]);
        }

        return $this->messageOptions;
    }

    /**
     * @param MessageOptions|array $messageOptions
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setMessageOptions($messageOptions)
    {
        if (is_array($messageOptions)) {
            $this->messageOptions = new MessageOptions($messageOptions);
        } elseif ($messageOptions instanceof MessageOptions) {
            $this->messageOptions = $messageOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'MessageOptions should be an array or an AcMailer\Options\MessageOptions object. %s provided.',
                is_object($messageOptions) ? get_class($messageOptions) : gettype($messageOptions)
            ));
        }

        return $this;
    }

    /**
     * @return SmtpOptions
     */
    public function getSmtpOptions()
    {
        if (! isset($this->smtpOptions)) {
            $this->setSmtpOptions([]);
        }

        return $this->smtpOptions;
    }

    /**
     * @param SmtpOptions|array $smtpOptions
     * @return $this
     */
    public function setSmtpOptions($smtpOptions)
    {
        if (is_array($smtpOptions)) {
            $this->smtpOptions = new SmtpOptions($smtpOptions);
        } elseif ($smtpOptions instanceof SmtpOptions) {
            $this->smtpOptions = $smtpOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'SmtpOptions should be an array or an Zend\Mail\Transport\SmtpOptions object. %s provided.',
                is_object($smtpOptions) ? get_class($smtpOptions) : gettype($smtpOptions)
            ));
        }

        return $this;
    }

    /**
     * @return FileOptions
     */
    public function getFileOptions()
    {
        if (! isset($this->fileOptions)) {
            $this->setFileOptions([]);
        }

        return $this->fileOptions;
    }

    /**
     * @param FileOptions|array $fileOptions
     * @return $this
     */
    public function setFileOptions($fileOptions)
    {
        if (is_array($fileOptions)) {
            $this->fileOptions = new FileOptions($fileOptions);
        } elseif ($fileOptions instanceof FileOptions) {
            $this->fileOptions = $fileOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'FileOptions should be an array or an Zend\Mail\Transport\FileOptions object. %s provided.',
                is_object($fileOptions) ? get_class($fileOptions) : gettype($fileOptions)
            ));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMailListeners()
    {
        return $this->mailListeners;
    }

    /**
     * @param array $mailListeners
     * @return $this
     */
    public function setMailListeners($mailListeners)
    {
        $this->mailListeners = (array) $mailListeners;
        return $this;
    }

    /**
     * @return string
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param string $renderer
     * @return $this
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
}
