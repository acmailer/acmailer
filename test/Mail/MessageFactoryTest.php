<?php
declare(strict_types=1);

namespace AcMailerTest\Mail;

use AcMailer\Mail\MessageFactory;
use AcMailer\Model\Email;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function messageCreatedFromEmailHasSameProperties(): void
    {
        $email = (new Email())->setFrom('me@domain.com')
                              ->setFromName('me')
                              ->setReplyTo('me-again@domain.com')
                              ->setReplyToName('me too')
                              ->setTo(['you@domain.com'])
                              ->addTo('you2@domain.com')
                              ->setCc(['you-copy@domain.com'])
                              ->addCc('you-copy2@domain.com')
                              ->setBcc(['you-blind@domain.com'])
                              ->addBcc('you-blind2@domain.com')
                              ->setEncoding('encoding')
                              ->setSubject('subject')
                              ->setBody('the body')
                              ->setCharset('utf-8')
                              ->setTemplateParams([]);

        $message = MessageFactory::createMessageFromEmail($email);

        $this->assertEquals('me@domain.com', $message->getFrom()->get('me@domain.com')->getEmail());
        $this->assertEquals('me', $message->getFrom()->get('me@domain.com')->getName());
        $this->assertEquals('me-again@domain.com', $message->getReplyTo()->get('me-again@domain.com')->getEmail());
        $this->assertEquals('me too', $message->getReplyTo()->get('me-again@domain.com')->getName());
        $this->assertEquals('you@domain.com', $message->getTo()->get('you@domain.com')->getEmail());
        $this->assertEquals('you2@domain.com', $message->getTo()->get('you2@domain.com')->getEmail());
        $this->assertEquals('you-copy@domain.com', $message->getCc()->get('you-copy@domain.com')->getEmail());
        $this->assertEquals('you-copy2@domain.com', $message->getCc()->get('you-copy2@domain.com')->getEmail());
        $this->assertEquals('you-blind@domain.com', $message->getBcc()->get('you-blind@domain.com')->getEmail());
        $this->assertEquals('you-blind2@domain.com', $message->getBcc()->get('you-blind2@domain.com')->getEmail());
        $this->assertEquals('encoding', $message->getEncoding());
        $this->assertEquals('subject', $message->getSubject());
    }
}
