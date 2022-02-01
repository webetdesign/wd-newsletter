<?php

namespace WebEtDesign\NewsletterBundle\EventListener;

use Exception;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Services\NewsletterContentCreatorService;

class NewsletterAdminListener
{
    public function __construct(
        private ModelProvider $provider,
        private NewsletterContentCreatorService $contentCreatorService
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

        $config = $this->provider->getConfigurationFor($newsletter->getModel());

        if (!$newsletter->getSender() || !$newsletter->getEmail()){
            $newsletter->setSender($config['sender'])
                ->setEmail($config['email']);
        }

        $this->contentCreatorService->createNewsletterContents($config, $newsletter);
    }

}
