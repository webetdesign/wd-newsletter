<?php

namespace WebEtDesign\NewsletterBundle\EventListener;

use Exception;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Services\NewsletterContentSynchronizer;

class NewsletterAdminListener
{
    public function __construct(
        private NewsletterContentSynchronizer $synchronizer
    ) {}

    /**
     * @throws Exception
     */
    public function prePersist($event)
    {
        $newsletter = $event->getObject();

        if (!$newsletter instanceof Newsletter) {
            return;
        }

        $config = $this->synchronizer->getModel($newsletter);

        if (!$newsletter->getSender() || !$newsletter->getEmail()){
            $newsletter->setSender($config->getSender())
                ->setEmail($config->getEmail());
        }

        $this->synchronizer->synchronize($newsletter);
    }

}
