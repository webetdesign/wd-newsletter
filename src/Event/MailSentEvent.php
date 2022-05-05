<?php

namespace WebEtDesign\NewsletterBundle\Event;

use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\Event;

class MailSentEvent extends Event
{

    public const NAME = 'NEWSLETTER_MAIL_SENT';

    public function __construct(private Email $message, private string $token, private ?int $newsletterId)
    {}

    /**
     * @return Email
     */
    public function getMessage(): Email
    {
        return $this->message;
    }

    /**
     * @param Email $message
     */
    public function setMessage(Email $message): void
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