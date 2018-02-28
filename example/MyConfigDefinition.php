<?php

class MyConfigDefinition extends \Keboola\Component\Config\BaseConfigDefinition
{
    protected function getParametersDefinition()
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
