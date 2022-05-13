<?php

namespace WebEtDesign\NewsletterBundle;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebEtDesign\NewsletterBundle\DependencyInjection\Compiler\NewsletterTemplatePass;
use WebEtDesign\NewsletterBundle\DependencyInjection\WDNewsletterBundleExtension;

/**
 * References:
 * @link http://symfony.com/doc/current/book/bundles.html
 */
class WDNewsletterBundle extends Bundle
{
    #[Pure] public function getContainerExtension(): WDNewsletterBundleExtension
    {
        return new WDNewsletterBundleExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new NewsletterTemplatePass());
    }


}
