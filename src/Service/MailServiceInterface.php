<?php
namespace AcMailer\Service;

use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Mail\Transport\TransportAwareInterface;
use AcMailer\Result\ResultInterface;
use AcMailer\View\DefaultLayoutInterface;
use AcMailer\View\RendererAwareInterface;

/**
 * Provides methods to be implemented by a valid MailService
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface MailServiceInterface extends TransportAwareInterface, RendererAwareInterface
{
    const DEFAULT_CHARSET = 'utf-8';

    /**
     * Tries to send the message, returning a MailResult object
     * @return ResultInterface
     */
    public function send();
    
    /**
     * Returns the message that is going to be sent when method send is called
     * @see \AcMailer\Service\MailServiceInterface::send()
     * @return \Zend\Mail\Message
     */
    public function getMessage();
    
    /**
     * Sets the message body
     * @param \Zend\Mime\Part|\Zend\Mime\Message|string $body
     * @param string $charset
     * @throws InvalidArgumentException
     */
    public function setBody($body, $charset = null);
    
    /**
     * Sets the template to be used to create the body of the email
     * @param string|\Zend\View\Model\ViewModel $template
     * @param array $params
     */
    public function setTemplate($template, array $params = []);

    /**
     * Sets the default layout to be used with all the templates set when calling setTemplate.
     *
     * @param DefaultLayoutInterface $layout
     * @return mixed
     */
    public function setDefaultLayout(DefaultLayoutInterface $layout = null);
    
    /**
     * Sets the message subject
     * @param string $subject
     * @deprecated Use $mailService->getMessage()->setSubject() instead
     */
    public function setSubject($subject);
    
    /**
     * Provides the path of a file that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param string $path
     * @param string|null $filename
     */
    public function addAttachment($path, $filename = null);

    /**
     * Provides an array of paths of files that will be attached to the message while sending it,
     * as well as other previously defined attachments
     * @param array $paths
     */
    public function addAttachments(array $paths);

    /**
     * Returns the list of attachments
     * @return array
     */
    public function getAttachments();
    
    /**
     * Sets the list of paths of files that will be attached to the message while sending it,
     * discarding any previously defined attachment
     * @param array $paths
     */
    public function setAttachments(array $paths);
}
