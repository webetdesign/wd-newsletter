<?php

namespace WebEtDesign\NewsletterBundle\Factory;

use Symfony\Component\DependencyInjection\ServiceLocator;
use WebEtDesign\CmsBundle\CMS\ConfigurationInterface;
use WebEtDesign\CmsBundle\CmsTemplate\TemplateInterface;
use WebEtDesign\CmsBundle\Factory\AbstractTemplateFactory;
use WebEtDesign\CmsBundle\Registry\TemplateRegistry;
use WebEtDesign\NewsletterBundle\Attribute\AbstractModel;

class NewsletterFactory extends TemplateRegistry
{
    private ServiceLocator          $serviceLocator;
    private array                   $configs;
    private ?ConfigurationInterface $configuration = null;

    public function __construct(ServiceLocator $templates, array $configs)
    {
        $this->serviceLocator = $templates;
        $this->configs        = $configs;
        parent::__construct($templates, $configs);
    }
    
    protected function mount($code): TemplateInterface
    {
        $config = $this->getConfig($code);

        return $this->getServices($code);
    }

    protected function getConfig($code): array
    {
        if (!isset($this->configs[$code])) {
            throw new \InvalidArgumentException(sprintf('Unknown model config "%s". The registered model configs are: %s',
                $code, implode(', ', array_keys($this->configs))));
        };

        return $this->configs[$code];
    }

    public function getConfigsList (): array
    {
        return $this->configs;
    }

    protected function getServices(string $code): TemplateInterface
    {
        if (!$this->serviceLocator->has($code)) {
            throw new \InvalidArgumentException(sprintf('Unknown model "%s". The registered model are: %s',
                $code, implode(', ', array_keys($this->serviceLocator->getProvidedServices()))));
        };

        return $this->serviceLocator->get($code);
    }
}
