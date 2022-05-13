<?php
/**
 * Created by PhpStorm.
 * User: jvaldena
 * Date: 22/01/2019
 * Time: 15:34
 */

namespace WebEtDesign\NewsletterBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use WebEtDesign\NewsletterBundle\Admin\NewsletterContentAdmin;
use WebEtDesign\NewsletterBundle\Admin\NewsletterNewsletterAdmin;
use WebEtDesign\NewsletterBundle\Attribute\AsNewsletterModel;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class WDNewsletterBundleExtension extends Extension
{
    /**
     * @throws Exception
     */
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


        $container->setParameter('wd_newsletter.enable_log', $config['enable_log']);
        $container->setParameter('wd_newsletter.routes', $config['routes']);
        $container->setParameter('wd_newsletter.noreply', $config['noreply']);
        $container->setParameter('wd_newsletter.replyTo', $config['replyTo']);
        $container->setParameter('wd_newsletter.locales', $config['locales']);
        $container->setParameter('wd_newsletter.admin.config.class.content', NewsletterContentAdmin::class);
        $container->setParameter('wd_newsletter.admin.config.class.newsletter', NewsletterNewsletterAdmin::class);

        $container->setParameter('wd_newsletter.entity.content', Content::class);
        $container->setParameter('wd_newsletter.entity.newsletter', Newsletter::class);
        $container->setParameter('wd_newsletter.entity.media', $config['class']['media']);
        $container->setParameter('wd_newsletter.entity.document', $config['class']['document']);
        $container->setParameter('wd_newsletter.entity.actuality', $config['class']['actuality']);

        if (method_exists($container, 'registerAttributeForAutoconfiguration')) {
            $container->registerAttributeForAutoconfiguration(AsNewsletterModel::class,
                static function (ChildDefinition $definition, AsNewsletterModel $attribute) {
                    $definition->addTag('wd_newsletter.model', array_filter([
                        'key'       => $attribute->code,
                    ]));
                }
            );
        }

        $container->getDefinition(NewsletterFactory::class)->setArguments([
            new ServiceLocatorArgument(new TaggedIteratorArgument('wd_newsletter.model', 'key',
                null, true)),
            []
        ]);

    }

    public function getAlias(): string
    {
        return 'wd-newsletter';
    }
}
