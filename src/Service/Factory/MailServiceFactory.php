<?php
namespace AcMailer\Service\Factory;

use AcMailer\Options\TemplateOptions;
use AcMailer\Util\Utils;
use Zend\ServiceManager\FactoryInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use AcMailer\Service\MailService;
use AcMailer\Options\MailOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * Constructs a new MailService injecting on it a Message and Transport object constructed with mail options
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        /* @var MailOptions $mailOptions */
        $mailOptions = $sm->get('AcMailer\Options\MailOptions');

        // Prepare Mail Message
        $message = new Message();
        $message->setFrom($mailOptions->getFrom(), $mailOptions->getFromName())
                ->setTo($mailOptions->getTo())
                ->setCc($mailOptions->getCc())
                ->setBcc($mailOptions->getBcc());

        // Prepare Mail Transport
        $transport = $mailOptions->getMailAdapter();
        if ($transport instanceof Smtp) {
            $connConfig = array(
                'username' => $mailOptions->getSmtpUser(),
                'password' => $mailOptions->getSmtpPassword(),
            );

            // Check if SSL should be used
            if ($mailOptions->getSsl() !== false) {
                $connConfig['ssl'] = $mailOptions->getSsl();
            }

            // Set SMTP transport options
            $transport->setOptions(new SmtpOptions(array(
                'host'              => $mailOptions->getServer(),
                'port'              => $mailOptions->getPort(),
                'connection_class'  => $mailOptions->getConnectionClass(),
                'connection_config' => $connConfig,
            )));
        }

        // Prepare MailService
        $renderer       = $sm->has('viewrenderer') ? $sm->get('viewrenderer') : new PhpRenderer();
        $mailService    = new MailService($message, $transport, $renderer);
        $mailService->setSubject($mailOptions->getSubject());

        // Set body, either by using a template or the body option
        $template = $mailOptions->getTemplate();
        if ($template->getUseTemplate() === true) {
            $mailService->setTemplate($template->toViewModel());
        } else {
            $mailService->setBody($mailOptions->getBody());
        }

        // Attach files
        $files = $mailOptions->getAttachments()->getFiles();
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $mailService->addAttachment($file);
        }
        // Attach files from dir
        $dir = $mailOptions->getAttachments()->getDir();
        if ($dir['iterate'] === true && is_string($dir['path']) && is_dir($dir['path'])) {
            $files = $dir['recursive'] === true ?
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir['path'], \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ):
                new \DirectoryIterator($dir['path']);

            /* @var \SplFileInfo $fileInfo */
            foreach ($files as $fileInfo) {
                if ($fileInfo->isDir()) {
                    continue;
                }
                $mailService->addAttachment($fileInfo->getPathname());
            }
        }

        return $mailService;
    }
}
