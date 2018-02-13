<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $this->getRootDefinition($treeBuilder);

        // @formatter:off
        $rootNode
            ->children()
                ->append($this->getParametersDefinition());
        // @formatter:on
        return $treeBuilder;
    }

    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        return $builder->root('parameters');
    }

    protected function getRootDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        $rootNode = $treeBuilder->root('root');
        $rootNode->ignoreExtraKeys(false);
        return $rootNode;
    }
}
