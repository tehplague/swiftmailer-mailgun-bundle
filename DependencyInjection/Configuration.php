<?php

namespace cspoo\Swiftmailer\MailgunBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mailgun');

        $this->addAPIConfigSection($rootNode);

        return $treeBuilder;
    }

    private function addAPIConfigSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
            ->scalarNode('key')
                ->isRequired()
            ->end()
            ->scalarNode('domain')
                ->isRequired()
            ->end();
    }
}
