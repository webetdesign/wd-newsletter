<?php

namespace WebEtDesign\NewsletterBundle\Messenger;

class EmailMessage
{
    public function __construct(
        private int $newsletterId,
        private array $emails
    )
    {
    }

    /**
     * @return int
     */
    public function getNewsletterId(): int
    {
        return $this->newsletterId;
    }

    /**
     * @return array
     */
    public function getEmails(): array
    {
        return $this->emails;
    }


}