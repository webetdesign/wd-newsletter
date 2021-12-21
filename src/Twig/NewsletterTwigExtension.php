<?php

namespace WebEtDesign\NewsletterBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * NewsletterTwigExtension constructor.
     * @param EmailService $emailService
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(
        EmailService $emailService,
        ContainerInterface $container,
        RequestStack $requestStack,
        EntityManagerInterface $em
    )
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->emailService = $emailService;
        // pb injection
        $this->modelProvider = new ModelProvider($this->container->getParameter('wd_newsletter.models'));
        $this->em = $em;
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
