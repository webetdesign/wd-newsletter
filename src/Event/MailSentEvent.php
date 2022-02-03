<?php

namespace WebEtDesign\NewsletterBundle\Event;

use Swift_Message;
use Symfony\Contracts\EventDispatcher\Event;

class MailSentEvent extends Event
{

    public const NAME = 'MAIL_SENT';

    public function __construct(private Swift_Message $message)
    {}

    /**
     * @return Swift_Message
     */
    public function getMessage(): Swift_Message
    {
        return $this->message;
    }

    /**
     * @param Swift_Message $message
     */
    public function setMessage(Swift_Message $message): void
    {
        $this->message = $message;
    }

}