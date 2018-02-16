<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $this->getRootDefinition($treeBuilder);

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getParametersDefinition()
    {
        $builder = new TreeBuilder();
        $parametersNode = $builder->root('parameters');
        $parametersNode->ignoreExtraKeys(false);
        return $parametersNode;
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getRootDefinition(TreeBuilder $treeBuilder)
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('root');
        $rootNode->ignoreExtraKeys(false);

        // @formatter:off
        $rootNode
            ->children()
                ->append($this->getParametersDefinition());
        // @formatter:on


        return $rootNode;
    }
}
