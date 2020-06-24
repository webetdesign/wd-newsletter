<?php

namespace WebEtDesign\NewsletterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebEtDesign\NewsletterBundle\DependencyInjection\WDNewsletterBundleExtension;

/**
 * References:
 * @link http://symfony.com/doc/current/book/bundles.html
 */
class WDNewsletterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WDNewsletterBundleExtension();
    }

}
