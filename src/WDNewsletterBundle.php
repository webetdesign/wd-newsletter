<?php

namespace WebEtDesign\NewsletterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebEtDesign\NewsletterBundle\DependencyInjection\NewsletterBundleExtension;

/**
 * References:
 * @link http://symfony.com/doc/current/book/bundles.html
 */
class WDNewsletterBundle extends Bundle
{
    protected function getContainerExtensionClass()
    {
        return NewsletterBundleExtension::class;
    }
}
