<?php
namespace AcMailer\Controller\Plugin;

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

    public function __construct(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * @param null|string|ViewModel|array $body
     * @param null|string $subject
     * @param null|array $to
     * @param null|array $from
     * @param null|array $cc
     * @param null|array $bcc
     * @param null|array $attachments
     */
    public function __invoke(
        $body = null,
        $subject = null,
        $to = null,
        $from = null,
        $cc = null,
        $bcc = null,
        $attachments = null
    ) {

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

    protected function normalizeMailArgs(array $args)
    {

    }
}
