<?php
namespace AcMailer\Mail\Transport;

use Zend\Mail\Transport\TransportInterface;

/**
 * Interface TransportAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface TransportAwareInterface
{
    /**
     * @param TransportInterface $transport
     * @return mixed
     */
    public function setTransport(TransportInterface $transport);

    /**
     * @return TransportInterface
     */
    public function getTransport();
}
