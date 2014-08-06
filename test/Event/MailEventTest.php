<?php
namespace AcMailerTest\Event;

use AcMailer\Event\MailEvent;
use AcMailer\Service\MailServiceMock;

/**
 * Class MailEventTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailEvent
     */
    private $mailEvent;

    public function testMailServiceInjection()
    {
        $mailService = new MailServiceMock();
        $this->mailEvent = new MailEvent($mailService);
        $this->assertSame($mailService, $this->mailEvent->getMailService());

        $mailService2 = new MailServiceMock();
        $this->mailEvent->setMailService($mailService2);
        $this->assertNotSame($mailService, $this->mailEvent->getMailService());
        $this->assertSame($mailService2, $this->mailEvent->getMailService());
    }
}
