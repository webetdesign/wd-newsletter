<?php

namespace WebEtDesign\NewsletterBundle\Messenger\Message;

use WebEtDesign\NewsletterBundle\Entity\Newsletter;

class NewsletterMailMessage
{
    private ?int   $newsletterId = null;
    private string $recipient;
    private string $token;
    private string $locale;

    public function __construct(Newsletter $newsletter, string $recipient, ?string $token, ?string $locale)
    {
        $this->newsletterId = $newsletter->getId();
        $this->recipient    = $recipient;
        $this->token        = $token;
        $this->locale       = $locale;

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

    /**
     * @param string $recipient
     * @return NewsletterMailMessage
     */
    public function setRecipient(string $recipient): NewsletterMailMessage
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
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
     * @return NewsletterMailMessage
     */
    public function setToken(string $token): NewsletterMailMessage
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return NewsletterMailMessage
     */
    public function setLocale(string $locale): NewsletterMailMessage
    {
        $this->locale = $locale;
        return $this;
    }
}