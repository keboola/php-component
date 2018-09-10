<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Config;

use ErrorException;
use Keboola\Component\Config\BaseConfig;
use Keboola\Component\Config\BaseConfigDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class BaseConfigDefinitionTest extends TestCase
{
    public function testConfigDefinitionDoesNotTouchKeys(): void
    {
        $definition = new BaseConfigDefinition();
        $config = new class ([
            'dash-key' => 'dash-value',
            'underscore_key' => 'underscore_value',
            'dot.key' => 'dot.value',
            'slash/key' => 'slash/value',
        ], $definition) extends BaseConfig
        {
            protected function checkKey(string $key): void
            {
                // intentionally empty to remove the error message
            }
        };

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
        $config = new class([
            'parameters' => [
                'dash-key' => 'dash-value',
                'underscore_key' => 'underscore_value',
                'slash/key' => 'slash/value',
            ],
        ], $definition) extends BaseConfig
        {
            protected function checkKey(string $key): void
            {
                // intentionally empty to remove the error message
            }
        };

        $this->assertSame('dash-value', $config->getValue(['parameters', 'dash-key']));
        $this->assertSame('underscore_value', $config->getValue(['parameters', 'underscore_key']));
        $this->assertSame('slash/value', $config->getValue(['parameters', 'slash/key']));
    }

    public function testWillRaiseDeprecationErrorForDashSeparatedKeys(): void
    {
        $definition = new BaseConfigDefinition();
        $config = new BaseConfig(['dash-key' => 'dash-value'], $definition);
        try {
            $config->getValue(['dash-key']);
            $this->fail('Should have thrown an exception');
        } catch (ErrorException $e) {
            $this->assertSame(\E_USER_DEPRECATED, $e->getSeverity());
            // phpcs:disable Generic.Files.LineLength
            $this->assertSame(
                'Try not to use dash-separated keys in config. You can override the "BaseConfig::checkKey" method to get rid of this message',
                $e->getMessage()
            );
            // phpcs:enable
        }
    }
}
