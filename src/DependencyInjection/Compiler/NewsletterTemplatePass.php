<?php

namespace WebEtDesign\NewsletterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class NewsletterTemplatePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $templateConfig = [];

        foreach ($container->findTaggedServiceIds('wd_newsletter.model') as $id => $tags) {
            foreach ($tags as $tag) {
                $templateConfig[$tag['key']] = [
                    'id'   => $id,
                    'code' => $tag['key'],
                    'type' => $tag['type']
                ];
            }
        }

        $templateFactory = $container->getDefinition(NewsletterFactory::class);
        $templateFactory->setArgument(1, $templateConfig);

    }
}
