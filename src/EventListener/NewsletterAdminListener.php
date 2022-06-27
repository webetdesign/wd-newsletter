<?php

namespace WebEtDesign\NewsletterBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use WebEtDesign\CmsBundle\CmsBlock\DynamicBlock;
use WebEtDesign\CmsBundle\Factory\TemplateFactoryInterface;
use WebEtDesign\NewsletterBundle\Attribute\AbstractModel;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;

class NewsletterAdminListener
{
    public function __construct(
        private TemplateFactoryInterface $templateFactory,
        private EntityManagerInterface $em,
        private array $locales = ['fr']
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

        /** @var AbstractModel $config */
        $config = $this->templateFactory->get($newsletter->getModel());

        if (!$newsletter->getSender() || !$newsletter->getEmail()){
            $newsletter->setSender($config->getSender())
                ->setEmail($config->getEmail());
        }

        $i = 0;
        foreach ($config->getBlocks() as $block) {
            if (!($content = $newsletter->getContent($block->getCode()))) {
                $content = new Content();
                $content->setHelp($block->getHelp());
                $content->setLabel($block->getLabel() ?? $block->getCode());
                $content->setType($block->getType());
                $content->setCode($block->getCode());
                $content->setPosition($i);

                $canTranslate = !in_array($content->getType(), [NewsletterContentTypeEnum::MEDIA]);
                $content->setCanTranslate($canTranslate);

                if ($canTranslate){
                    if ($content->getCanTranslate()){
                        $locales = $this->locales;
                    }else{
                        $locales = ['fr'];
                    }

                    foreach ($locales as $locale) {
                        $newsletterContent_trans = new ContentTranslation();
                        $newsletterContent_trans->setLocale($locale);
                        $newsletterContent_trans->setTranslatable($content);
                        $this->em->persist($newsletterContent_trans);
                    }
                }
                $CmsContent = new Content();
                $CmsContent->setCode($block->getCode());
                $CmsContent->setLabel($block->getLabel());
                $CmsContent->setType($block->getType());
            }
            $i++;
            $newsletter->addContent($content);
        }

    }

}
