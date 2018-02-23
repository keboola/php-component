<?php declare(strict_types = 1);

namespace Keboola\Component\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * ConfigDefinition specifies the bare minimum of what should a config contain.
 * It's a best practice to extend it and define all parameters required by your code.
 * That way you can be sure that your code has all the data it needs and it can fail fast
 * otherwise. Usually your code requires some parameters, so it's easiest to extend this
 * class and just override `getParametersDefinition()` method.
 */
class KeboolaConfigDefinition implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder. You probably don't need to touch this.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $this->getRootDefinition($treeBuilder);
        return $treeBuilder;
    }

    /**
     * Definition of parameters section. Override in extending class to validate parameters sent to the component early.
     *
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getParametersDefinition()
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $parametersNode */
        $parametersNode = $builder->root('parameters');
        $parametersNode->ignoreExtraKeys(false);
        return $parametersNode;
    }

    /**
     * Root definition to be overridden in special cases
     *
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
