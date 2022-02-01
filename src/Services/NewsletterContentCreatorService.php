<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;

class NewsletterContentCreatorService
{
    public function __construct(private array $locales, private EntityManagerInterface $em) {}

    public function createNewsletterContents(array $config, Newsletter $newsletter): Newsletter
    {
        foreach ($config['contents'] as $content) {
            $newsletterContent = $newsletter->getContent($content['code']);
            if (!$newsletterContent){
                $newsletterContent = new Content();
                $newsletterContent->setHelp($content['help']);
                $newsletterContent->setLabel($content['label'] ?: $content['code']);
                $newsletterContent->setType($content['type']);
                $newsletterContent->setCode($content['code']);
                $newsletterContent->setCanTranslate($content['translate']);

                if ($newsletterContent->getType() !== NewsletterContentTypeEnum::MEDIA ){
                    if ($content['translate']){
                        $locales = $this->locales;
                    }else{
                        $locales = ['fr'];
                    }

                    foreach ($locales as $locale) {
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
