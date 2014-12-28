<?php
namespace AcMailer\Controller\Plugin;

use AcMailer\Options\MailOptions;
use AcMailer\Service\MailServiceAwareInterface;
use AcMailer\Service\MailServiceInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class MailPlugin
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailPlugin extends AbstractPlugin implements MailServiceAwareInterface
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
     * @param MailOptions|array $options
     * @return MailServiceInterface
     */
    public function __invoke($options = null)
    {
        if (isset($options)) {
            if (is_array($options)) {
                $options = new MailOptions($options);
            }

            $this->configServiceFromOptions($options);
        }

        return $this->getMailService();
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

    /**
     * Configures wraped mail service based in provided config
     * @param MailOptions $options
     */
    protected function configServiceFromOptions(MailOptions $options)
    {

    }
}
