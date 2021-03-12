<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 * @group init
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('d_media');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('filterable_entities')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                    ->end()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_url')->defaultValue('/storage/uploads')->end()
                        ->scalarNode('directory')->defaultValue('uploads')->end()
                    ->end()
                ->end()
                ->arrayNode('library')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_extensions')->defaultValue('png, jpg, jpeg, webp')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
