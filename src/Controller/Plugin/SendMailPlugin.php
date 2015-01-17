<?php
namespace AcMailer\Controller\Plugin;

use AcMailer\Result\ResultInterface;
use AcMailer\Service\MailServiceAwareInterface;
use AcMailer\Service\MailServiceInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

/**
 * Class SendMailPlugin
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class SendMailPlugin extends AbstractPlugin implements MailServiceAwareInterface
{
    /**
     * @var MailServiceInterface
     */
    protected $mailService;

    /**
     * The list of possible arguments in the order they should be provided
     * @var array
     */
    private $argumentsMapping = [
        0 => 'body',
        1 => 'subject',
        2 => 'to',
        3 => 'from',
        4 => 'cc',
        5 => 'bcc',
        6 => 'attachments'
    ];

    public function __construct(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * If no arguments are provided, the mail service is returned.
     * If any argument is provided, they will be used to configure the MailService and send an email.
     * The result object will be returned in that case
     *
     * @param null|string|ViewModel|array $bodyOrConfig
     * @param null|string $subject
     * @param null|array $to
     * @param null|string|array $from
     * @param null|array $cc
     * @param null|array $bcc
     * @param null|array $attachments
     * @return MailServiceInterface|ResultInterface
     */
    public function __invoke(
        $bodyOrConfig = null,
        $subject = null,
        $to = null,
        $from = null,
        $cc = null,
        $bcc = null,
        $attachments = null
    ) {
        $args = func_get_args();
        if (empty($args)) {
            return $this->mailService;
        }

        $args = $this->normalizeMailArgs($args);
        $this->applyArgsToMailService($args);
        return $this->mailService->send();
    }

    /**
     * Normalizes the arguments passed when invoking this plugin so that they can be treated in a consistent way
     *
     * @param array $args
     * @return array
     */
    protected function normalizeMailArgs(array $args)
    {
        // If the first argument is an array, use it as the mail configuration
        if (is_array($args[0])) {
            return $args[0];
        }

        $result = [];
        $length = count($args);
        // FIXME This is a weak way to handle the arguments, since a change in the order will break it
        for ($i = 0; $i < $length; $i++) {
            $result[$this->argumentsMapping[$i]] = $args[$i];
        }

        return $result;
    }

    /**
     * Applies the arguments provided while invoking this plugin to the MailService,
     * discarding any previous configuration
     *
     * @param array $args
     */
    protected function applyArgsToMailService(array $args)
    {
        if (isset($args['body'])) {
            $body = $args['body'];

            if (is_string($body)) {
                $this->mailService->setBody($body);
            } else {
                $this->mailService->setTemplate($body);
            }
        }

        if (isset($args['subject'])) {
            $this->mailService->setSubject($args['subject']);
        }

        if (isset($args['to'])) {
            $this->mailService->getMessage()->setTo($args['to']);
        }

        if (isset($args['from'])) {
            $from = $args['from'];

            if (is_array($from)) {
                $fromAddress = array_keys($from);
                $fromName = array_values($from);
                $this->mailService->getMessage()->setFrom($fromAddress[0], $fromName[0]);
            } else {
                $this->mailService->getMessage()->setFrom($from);
            }
        }

        if (isset($args['cc'])) {
            $this->mailService->getMessage()->setCc($args['cc']);
        }

        if (isset($args['bcc'])) {
            $this->mailService->getMessage()->setBcc($args['bcc']);
        }

        if (isset($args['attachments'])) {
            $this->mailService->setAttachments($args['attachments']);
        }
    }

    /**
     * @param MailServiceInterface $mailService
     * @return $this
     */
    public function setMailService(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
        return $this;
    }

    /**
     * @return MailServiceInterface
     */
    public function getMailService()
    {
        return $this->mailService;
    }
}
