<?php

namespace WebEtDesign\NewsletterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class NewsletterTemplatePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $templateConfig            = [];

        foreach ($container->findTaggedServiceIds('wd_newsletter.model') as $id => $tags) {
            $definition = $container->findDefinition($id);

            $definition->setPublic(true);

            foreach ($tags as $tag) {
                $templateConfig[$tag['key']] = array_filter(['code' => $tag['key']]);
            }
        }

        $templateFactory = $container->getDefinition(NewsletterFactory::class);
        $templateFactory->setArgument(1, $templateConfig);

    }
}
