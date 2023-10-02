<?php
/**
 * Created by PhpStorm.
 * User: jvaldena
 * Date: 22/01/2019
 * Time: 16:27
 */

namespace WebEtDesign\NewsletterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wd-newsletter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('mailer')->isRequired()->end()
                ->scalarNode('enable_log')->defaultFalse()->end()
                ->scalarNode('send_by_messenger')->defaultFalse()->end()
                ->arrayNode('locales')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return preg_split('/\s*,\s*/', $v);
                        })
                    ->end()
                    ->requiresAtLeastOneElement()
                        ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('class')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('media')->cannotBeEmpty()->end()
                        ->scalarNode('document')->defaultNull()->end()
                        ->scalarNode('actuality')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('routes')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('home')->defaultValue('index')->end()
                    ->end()
                ->end()
                ->arrayNode('noreply')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')->cannotBeEmpty()->end()
                        ->scalarNode('name')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('replyTo')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')->defaultNull()->end()
                        ->scalarNode('name')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('models')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->scalarNode('sender')->cannotBeEmpty()->end()
                            ->scalarNode('email')->cannotBeEmpty()->end()
                            ->scalarNode('template')->cannotBeEmpty()->end()
                            ->scalarNode('txt')->cannotBeEmpty()->end()
                            ->arrayNode('contents')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('translate')->defaultTrue()->end()
                                        ->scalarNode('code')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('label')->cannotBeEmpty()->end()
                                        ->scalarNode('help')->defaultNull()->end()
                                        ->scalarNode('type')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
