<?php

namespace WebEtDesign\NewsletterBundle\Factory;

use WebEtDesign\CmsBundle\CmsTemplate\TemplateInterface;
use WebEtDesign\CmsBundle\Factory\AbstractTemplateFactory;
use WebEtDesign\NewsletterBundle\Attribute\AbstractModel;

class NewsletterFactory extends AbstractTemplateFactory
{
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
