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
                ->arrayNode('cdn')
                    ->children()
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
            ->scalarNode('name_route_unauthorized')->defaultValue('Seo_unauthorized')->end()
            ->scalarNode('timer')->defaultValue(3600)->end()
            ->scalarNode('check_vpn')->defaultValue(false)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}