<?php

namespace WebEtDesign\NewsletterBundle\Twig;

use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use WebEtDesign\NewsletterBundle\Services\EmailService;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;

class NewsletterTwigExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /** @var EmailService $emailService */
    private $emailService;

    /** @var ModelProvider $modelProvider */
    private $modelProvider;

    /**
     * NewsletterTwigExtension constructor.
     * @param EmailService $emailService
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(EmailService $emailService, ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->emailService = $emailService;
        // pb injection
        $this->modelProvider = new ModelProvider($this->container->getParameter('wd_newsletter.models'));
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

    public function renderContent($object, $content_code, $locale = 'fr'){
        if ($object instanceof Newsletter){
            /** @var Content|null $content */
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

                    return $content->translate($locale)->getValue();
            }

        }

        return null;
    }

    public function renderMedia($object, $content_code, $format){
        if ($object instanceof Newsletter){
            /** @var Content|null $content */
            $content = $object->getContent($content_code);
            if (!$content || $content->getType() !== NewsletterContentTypeEnum::MEDIA || !$content->getMedia()){
                return null;
            }

            /** @var ImageProvider $provider */
            $provider = $this->container->get($content->getMedia()->getProviderName());
            return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . $provider->generatePublicUrl($content->getMedia(), $provider->getFormatName($content->getMedia(), $format));
        }

        return null;
    }

    public function countUsers($object){
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

    public function getTemplate($model){
        return $this->modelProvider->getTemplate($model);
    }

    public function getLocales(){
        return $this->container->getParameter('wd_newsletter.locales');
    }

}
