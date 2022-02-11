<?php

namespace WebEtDesign\NewsletterBundle\Event;

use Swift_Message;
use Symfony\Contracts\EventDispatcher\Event;

class MailSentEvent extends Event
{

    public const NAME = 'NEWSLETTER_MAIL_SENT';

    public function __construct(private Swift_Message $message, private string $token, private ?int $newsletterId)
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

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return int|null
     */
    public function getNewsletterId(): ?int
    {
        return $this->newsletterId;
    }

    /**
     * @param int|null $newsletterId
     */
    public function setNewsletterId(?int $newsletterId): void
    {
        $this->newsletterId = $newsletterId;
    }

}