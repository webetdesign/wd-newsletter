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
use Symfony\Component\HttpFoundation\Request;
use WebEtDesign\CmsBundle\Entity\CmsGlobalVarsDelimiterEnum;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wd-newsletter');

        $rootNode
            ->children()
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
                    ->end()
                ->end()
                ->arrayNode('routes')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('home')->defaultValue('index')->end()
                    ->end()
                ->end()
                ->arrayNode('roles')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('noreply')->cannotBeEmpty()->end()
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
