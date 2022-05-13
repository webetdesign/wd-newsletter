<?php

namespace WebEtDesign\NewsletterBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WebEtDesign\MediaBundle\Services\WDMediaService;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class NewsletterTwigExtension extends AbstractExtension
{
    public function __construct(
        private EmailService $emailService,
        private ContainerInterface $container,
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private WDMediaService $mediaService
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('newsletter_render_content', [$this, 'renderContent'],
                ['is_safe' => ['html']]),
            new TwigFunction('newsletter_render_media', [$this, 'renderMedia'],
                ['is_safe' => ['html']]),
            new TwigFunction('count_users', [$this, 'countUsers'],
                ['is_safe' => ['html']]),
            new TwigFunction('get_model_title', [$this, 'getModelTitle'],
                ['is_safe' => ['html']]),
            new TwigFunction('get_model_template', [$this, 'getTemplate'],
                ['is_safe' => ['html']]),
            new TwigFunction('get_locales', [$this, 'getLocales'],
                ['is_safe' => ['html']]),
        ];
    }

    public function renderContent($object, $content_code, $locale = 'fr'): array|string|null
    {
        if ($object instanceof Newsletter){
            $content = $object->getContent($content_code);
            if (!$content){
                return null;
            }
            switch ($content->getType()){
                case NewsletterContentTypeEnum::WYSYWYG;
                case NewsletterContentTypeEnum::TEXT;
                case NewsletterContentTypeEnum::TEXTAREA;
                case NewsletterContentTypeEnum::COLOR;
                    if (!$content->getCanTranslate()){
                        $locale = 'fr';
                    }

                    $base = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

                    return preg_replace('~(?:src|action|href)=[\'"]\K/(?!/)[^\'"]*~',"$base$0", $content->translate($locale)->getValue());
                case NewsletterContentTypeEnum::DOCUMENTS:
                case NewsletterContentTypeEnum::ACTUALITIES:
                $er = $this->em->getRepository(
                    $content->getType() === NewsletterContentTypeEnum::ACTUALITIES ?
                        $this->container->getParameter('wd_newsletter.entity.actuality') :
                        $this->container->getParameter('wd_newsletter.entity.document')
                );
                $data = [];
                foreach (explode(',', $content->translate($locale)->getValue()) as $id) {
                    $object = $er->find($id);
                    if ($object){
                        $data[] = $object;
                    }
                }
                return $data;
            }

        }

        return null;
    }

    public function renderMedia($object, $content_code): ?string
    {
        if ($object instanceof Newsletter){
            /** @var Content|null $content */
            $content = $object->getContent($content_code);
            if (!$content || $content->getType() !== NewsletterContentTypeEnum::MEDIA || !$content->getMedia()){
                return null;
            }

            return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . $this->mediaService->getMediaPath($content->getMedia());
        }

        return null;
    }

    public function countUsers($object): int
    {
        $total = 0;

        if ($object instanceof Newsletter){
            $emails = $this->emailService->getEmails($object);
            foreach ($emails as $locale) {
                $total += count($locale);
            }
        }

        return $total;
    }

    public function getModelTitle($model){
        $models = $this->container->getParameter('wd_newsletter.models');
        return isset($models[$model]) ? $models[$model]['name'] : $model;
    }

    /**
     * @throws Exception
     */
    public function getTemplate($model){
        return $this->modelProvider->getTemplate($model);
    }

    public function getLocales(): array
    {
        return $this->container->getParameter('wd_newsletter.locales');
    }

}
