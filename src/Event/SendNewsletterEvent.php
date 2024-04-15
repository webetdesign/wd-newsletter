<?php

namespace WebEtDesign\NewsletterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;

class SendNewsletterEvent extends Event
{
    public const NAME = 'NEWSLETTER_MAIL_SEND';
    
    public function __construct(protected Newsletter $newsletter) { }

    /**
     * @return GreetingCard
     */
    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }
}
