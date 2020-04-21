<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ROOT_ID = 'huh_entity_import';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(static::ROOT_ID);
        $rootNode
            ->children()
                ->arrayNode('debug')
                    ->children()
                        ->booleanNode('contao_log')->defaultTrue()->end()
                        ->booleanNode('email')->defaultFalse()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
