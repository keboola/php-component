<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Config;

use Keboola\Component\Config\BaseConfig;
use Keboola\Component\Config\BaseConfigDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class BaseConfigDefinitionTest extends TestCase
{
    public function testConfigDefinitionDoesNotTouchKeys(): void
    {
        $definition = new BaseConfigDefinition();
        $config = new BaseConfig([
            'dash-key' => 'dash-value',
            'underscore_key' => 'underscore_value',
            'dot.key' => 'dot.value',
            'slash/key' => 'slash/value',
        ], $definition);

        $this->assertSame('dash-value', $config->getValue(['dash-key']));
        $this->assertSame('underscore_value', $config->getValue(['underscore_key']));
        $this->assertSame('dot.value', $config->getValue(['dot.key']));
        $this->assertSame('slash/value', $config->getValue(['slash/key']));
    }

    public function testConfigDefinitionDoesNotTouchKeysInDeepStructure(): void
    {
        $definition = new class() extends BaseConfigDefinition
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $parametersDefinition = parent::getParametersDefinition();
                $parametersDefinition->children()
                    ->scalarNode('dash-key')->end()
                    ->scalarNode('underscore_key')->end()
                    ->scalarNode('slash/key')->end()
                    ->end()
                ;
                return $parametersDefinition;
            }
        };
        $config = new BaseConfig([
            'parameters' => [
                'dash-key' => 'dash-value',
                'underscore_key' => 'underscore_value',
                'slash/key' => 'slash/value',
            ],
        ], $definition);

        $this->assertSame('dash-value', $config->getValue(['parameters', 'dash-key']));
        $this->assertSame('underscore_value', $config->getValue(['parameters', 'underscore_key']));
        $this->assertSame('slash/value', $config->getValue(['parameters', 'slash/key']));
    }
}
