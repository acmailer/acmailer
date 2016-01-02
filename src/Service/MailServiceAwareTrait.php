<?php
namespace AcMailer\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class MailServiceAwareTrait
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
trait MailServiceAwareTrait
{
    /**
     * @var MailServiceInterface
     */
    protected $mailService;

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
        if ($this instanceof ServiceLocatorAwareInterface
            && ! isset($this->mailService)
            && $this->getServiceLocator()->has('AcMailer\Service\MailService')
        ) {
            $this->mailService = $this->getServiceLocator()->get('AcMailer\Service\MailService');
        }

        return $this->mailService;
    }
}
