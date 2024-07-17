<?php



/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NeoxGeolocator\NeoxGeolocatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neox_geolocator');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('ip_local_dev')->defaultValue(null)->end()
            ->scalarNode('custome_api')->defaultValue(null)->end()
            ->scalarNode('check_vpn')->defaultValue(null)->end()
            ->scalarNode('forcer')->defaultFalse()->end()
            ->arrayNode('check_ping')
                ->children()
                    ->scalarNode('on')->defaultFalse()->end()
                    ->scalarNode('expire')->defaultValue(10)->end()
                    ->scalarNode('ping')->defaultValue(5)->end()
                    ->scalarNode('banni')->defaultValue(600)->end()
                ->end()
            ->end()
            ->arrayNode('cdn')
                ->children()
                    ->scalarNode('api_use')->defaultValue("standart")->end()
                    ->scalarNode('api_key')->defaultValue("xxxxxxxx")->end()
                    ->scalarNode('ip')->defaultValue("https://ipecho.net/plain")->end()
                    ->scalarNode('ip_info')->defaultValue("http://ip-api.com/json/")->end()
                    ->scalarNode('check_vpn')->defaultValue("http://check.getipintel.net/check.php")->end()
                    ->scalarNode('around')->defaultValue("https://www.villes-voisines.fr/getcp.php?")->end()
                ->end()
            ->end()
            ->arrayNode('filter')
                ->children()
                    ->arrayNode('local')
                        ->scalarPrototype()->defaultValue('fr')->end()
                    ->end()
                    ->arrayNode('connection')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('continents')
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('crawler')->scalarPrototype()->end()->end()
            ->arrayNode('name_route_exclude')->scalarPrototype()->end()->end()
            ->arrayNode('filter_local_range_ip')->scalarPrototype()->end()->end()
            ->scalarNode('name_route_unauthorized')->defaultValue('Seo_unauthorized')->end()
            ->scalarNode('timer')->defaultValue(3600)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}