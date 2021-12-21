<?php
/**
 * Created by PhpStorm.
 * User: jvaldena
 * Date: 22/01/2019
 * Time: 15:34
 */

namespace WebEtDesign\NewsletterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use WebEtDesign\NewsletterBundle\Admin\NewsletterContentAdmin;
use WebEtDesign\NewsletterBundle\Admin\NewsletterNewsletterAdmin;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;

class WDNewsletterBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');
        $loader->load('admin.yaml');
        $loader->load('listener.yaml');
        $loader->load('command.yaml');


        $container->setParameter('wd_newsletter.mailer', $config['mailer']);
        $container->setParameter('wd_newsletter.models', $config['models']);
        $container->setParameter('wd_newsletter.routes', $config['routes']);
        $container->setParameter('wd_newsletter.roles', $config['roles']);
        $container->setParameter('wd_newsletter.noreply', $config['noreply']);
        $container->setParameter('wd_newsletter.locales', $config['locales']);
        $container->setParameter('wd_newsletter.admin.config.class.content', NewsletterContentAdmin::class);
        $container->setParameter('wd_newsletter.admin.config.class.newsletter', NewsletterNewsletterAdmin::class);

        $container->setParameter('wd_newsletter.entity.content', Content::class);
        $container->setParameter('wd_newsletter.entity.newsletter', Newsletter::class);
        $container->setParameter('wd_newsletter.entity.media', $config['class']['media']);
        $container->setParameter('wd_newsletter.entity.document', $config['class']['document']);
        $container->setParameter('wd_newsletter.entity.actuality', $config['class']['actuality']);

    }

    public function getAlias()
    {
        return 'wd-newsletter';
    }
}
