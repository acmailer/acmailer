<?php

declare(strict_types=1);

namespace AcMailer\Mail;

use AcMailer\Model\Email;
use Zend\Mail\Message;

final class MessageFactory
{
    private function __construct()
    {
    }

    public static function createMessageFromEmail(Email $email): Message
    {
        // Prepare Mail Message
        $message = new Message();
        $from = $email->getFrom();
        if (! empty($from)) {
            $message->setFrom($from, $email->getFromName());
        }
        $replyTo = $email->getReplyTo();
        if (! empty($replyTo)) {
            $message->setReplyTo($replyTo, $email->getReplyToName());
        }
        $to = $email->getTo();
        if (! empty($to)) {
            $message->setTo($to);
        }
        $cc = $email->getCc();
        if (! empty($cc)) {
            $message->setCc($cc);
        }
        $bcc = $email->getBcc();
        if (! empty($bcc)) {
            $message->setBcc($bcc);
        }
        $encoding = $email->getEncoding();
        if (! empty($encoding)) {
            $message->setEncoding($encoding);
        }
        $subject = $email->getSubject();
        if (! empty($subject)) {
            $message->setSubject($subject);
        }

        return $message;
    }
}
