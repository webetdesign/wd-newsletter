<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;

class NewsletterContentCreatorService
{
    /** @var array $locales */
    private $locales;

    /** @var EntityManagerInterface $em */
    private $em;

    /**
     * NewsletterContentCreatorService constructor.
     * @param array $locales
     * @param EntityManagerInterface $em
     */
    public function __construct(array $locales, EntityManagerInterface $em)
    {
        $this->locales = $locales;
        $this->em      = $em;
    }

    public function createNewsletterContents(array $config, Newsletter $newsletter){
        foreach ($config['contents'] as $content) {
            $newsletterContent = $newsletter->getContent($content['code']);
            if (!$newsletterContent){
                $newsletterContent = new Content();
                $newsletterContent->setHelp($content['help']);
                $newsletterContent->setLabel($content['label'] ? $content['label'] : $content['code']);
                $newsletterContent->setType($content['type']);
                $newsletterContent->setCode($content['code']);
                if ($newsletterContent->getType() !== NewsletterContentTypeEnum::MEDIA){
                    foreach ($this->locales as $locale) {
                        $newsletterContent_trans = new ContentTranslation();
                        $newsletterContent_trans->setLocale($locale);
                        $newsletterContent_trans->setTranslatable($newsletterContent);
                        $this->em->persist($newsletterContent_trans);

                    }
                }
            }
            $newsletter->addContent($newsletterContent);
        }

        return $newsletter;
    }
}
