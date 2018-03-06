<?php declare(strict_types = 1);

namespace MyComponent;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class MyConfigDefinition extends \Keboola\Component\Config\BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        $parametersNode
            ->isRequired()
            ->children()
                ->arrayNode('errorCount')
                    ->isRequired()
                    ->children()
                        ->integerNode('maximumAllowed')
                            ->isRequired();
        return $parametersNode;
    }
}
