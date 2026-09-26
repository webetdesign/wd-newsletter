<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use WebEtDesign\CmsBundle\Registry\TemplateRegistryInterface;
use WebEtDesign\NewsletterBundle\Attribute\AbstractModel;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;

/**
 * Adds to a newsletter the contents its model declares and it does not have yet.
 *
 * Shared by the prePersist listener (new newsletters) and the
 * newsletter:sync-contents command (existing ones, after a model gained blocks).
 */
class NewsletterContentSynchronizer
{
    public function __construct(
        private TemplateRegistryInterface $templateFactory,
        private EntityManagerInterface $em,
        private array $locales = ['fr']
    ) {}

    public function getModel(Newsletter $newsletter): AbstractModel
    {
        return $this->templateFactory->get($newsletter->getModel());
    }

    public function synchronize(Newsletter $newsletter): void
    {
        $config = $this->getModel($newsletter);

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

                if ($canTranslate) {
                    foreach ($this->locales as $locale) {
                        $translation = new ContentTranslation();
                        $translation->setLocale($locale);
                        $translation->setTranslatable($content);
                        $this->em->persist($translation);
                    }
                }
            }
            $i++;
            $newsletter->addContent($content);
        }
    }
}
