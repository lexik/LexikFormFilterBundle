<?php

namespace Lexik\Bundle\FormFilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('lexik_form_filter');
        $rootNode
            ->children()
                ->arrayNode('listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('doctrine_dbal')->defaultFalse()->end()
                        ->booleanNode('doctrine_orm')->defaultTrue()->end()
                        ->booleanNode('doctrine_mongodb')->defaultFalse()->end()
                    ->end()
                ->end()

                ->scalarNode('where_method')
                    ->defaultValue('and')
                    ->info('Defined the doctrine query builder method the bundle will use to add the entire filter condition.')
                    ->validate()
                        ->ifNotInArray(array(null, 'and', 'or'))
                        ->thenInvalid('Invalid value, please use "null", "and", "or".')
                    ->end()
                ->end()

                ->booleanNode('force_case_insensitivity')
                    ->info('Whether to do case insensitive LIKE comparisons.')
                    ->defaultNull()
                ->end()

                ->scalarNode('encoding')
                    ->info('Encoding for case insensitive LIKE comparisons.')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
