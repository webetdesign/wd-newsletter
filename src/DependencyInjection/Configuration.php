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
        $treeBuilder = new TreeBuilder('wd_newsletter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('enable_log')->defaultFalse()->end()
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
                        ->scalarNode('user')->cannotBeEmpty()->end()
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
            ->end();

        return $treeBuilder;
    }
}
