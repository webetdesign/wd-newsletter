<?php

namespace WebEtDesign\NewsletterBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Services\NewsletterContentCreatorService;

class NewsletterAdminListener
{
    protected $provider;
    /**
     * @var NewsletterContentCreatorService
     */
    private $contentCreatorService;

    /**
     * NewsletterAdminListener constructor.
     * @param ModelProvider $provider
     * @param NewsletterContentCreatorService $contentCreatorService
     */
    public function __construct(
        ModelProvider $provider,
        NewsletterContentCreatorService $contentCreatorService
    ) {
        $this->provider            = $provider;
        $this->contentCreatorService = $contentCreatorService;
    }

    // create model form template configuration
    public function prePersist($event)
    {
        $newsletter = $event->getObject();

        if (!$newsletter instanceof Newsletter) {
            return;
        }
        $config = $this->provider->getConfigurationFor($newsletter->getModel());
        $newsletter->setSender($config['sender'])
            ->setEmail($config['email']);

        $newsletter = $this->contentCreatorService->createNewsletterContents($config, $newsletter);

    }

}
