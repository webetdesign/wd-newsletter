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

    /**
     * NewsletterTwigExtension constructor.
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('newsletter_render_content', [$this, 'renderContent'],
                ['is_safe' => ['html']]),
            new TwigFunction('newsletter_render_media', [$this, 'renderMedia'],
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
                    return $content->translate($locale)->getValue();
            }

        }

        return null;
    }

    public function renderMedia($object, $content_code, $format){
        if ($object instanceof Newsletter){
            /** @var Content|null $content */
            $content = $object->getContent($content_code);
            if (!$content || $content->getType() !== NewsletterContentTypeEnum::MEDIA){
                return null;
            }

            /** @var ImageProvider $provider */
            $provider = $this->container->get($content->getMedia()->getProviderName());
            return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . $provider->generatePublicUrl($content->getMedia(), $provider->getFormatName($content->getMedia(), $format));
        }

        return null;

    }

}
