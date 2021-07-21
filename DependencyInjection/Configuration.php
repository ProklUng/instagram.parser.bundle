<?php

namespace Prokl\InstagramParserRapidApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Prokl\InstagramParserRapidApiBundle\DependencyInjection
 *
 * @since 04.12.2020
 *
 * @psalm-suppress PossiblyUndefinedMethod
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rapid_api_instagram_parser');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('defaults')
            ->useAttributeAsKey('name')
                ->prototype('boolean')->end()
                ->defaultValue([
                    'enabled' => false,
                ])
                ->end()
            ->end()

            ->children()
                ->booleanNode('mock')->defaultValue(false)->end()
                ->scalarNode('instagram_user_id')->end()
                ->scalarNode('instagram_user_name')->defaultValue('')->end()
                ->scalarNode('rapid_api_key')->end()
                ->scalarNode('path_image')->defaultValue('/upload/instagram')->end()
                ->scalarNode('fixture_response_path')->defaultValue(
                    '/local/config/Fixture/response.txt'
                )->end()
                ->scalarNode('fixture_user_path')->defaultValue(
                    '/local/config/Fixture/user.txt'
                )->end()
                ->scalarNode('cacher_service')->defaultValue('')->end()
                ->scalarNode('cache_path')->defaultValue('cache/instagram-parser')->end()
                ->scalarNode('cache_ttl')->defaultValue(86400)->end()
                ->scalarNode('cache_user_data_ttl')->defaultValue(31536000)->end()
            ->end();

        return $treeBuilder;
    }
}
