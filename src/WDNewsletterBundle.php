<?php

namespace WebEtDesign\NewsletterBundle;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpKernel\Bundle\Bundle;
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

}
