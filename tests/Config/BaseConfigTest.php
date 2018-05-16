<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Config;

use Keboola\Component\Config\BaseConfig;
use Keboola\Component\Config\BaseConfigDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class BaseConfigTest extends TestCase
{
    public function testWillCreateConfigFromArray(): void
    {
        $config = new BaseConfig([]);

        $this->assertInstanceOf(BaseConfig::class, $config);
    }

    public function testCanOverrideParametersDefinition(): void
    {
        $configDefinition = new class extends BaseConfigDefinition implements ConfigurationInterface
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $nodeDefinition = parent::getParametersDefinition();
                // @formatter:off
                $nodeDefinition->isRequired();
                $nodeDefinition
                    ->children()
                        ->scalarNode('requiredValue')
                            ->isRequired()
                            ->cannotBeEmpty();
                // @formatter:on
                return $nodeDefinition;
            }
        };

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "requiredValue" at path "root.parameters" must be configured.');

        new BaseConfig(['parameters' => []], $configDefinition);
    }

    public function testStrictParametersCheck(): void
    {
        $configDefinition = new class extends BaseConfigDefinition implements ConfigurationInterface
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $nodeDefinition = parent::getParametersDefinition();
                // @formatter:off
                $nodeDefinition->isRequired();
                $nodeDefinition
                    ->children()
                    ->scalarNode('requiredValue')
                    ->isRequired()
                    ->cannotBeEmpty();
                // @formatter:on
                return $nodeDefinition;
            }
        };

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unrecognized option "extraValue" under "root.parameters"');

        new BaseConfig(['parameters' => ['requiredValue' => 'yes', 'extraValue' => 'no']], $configDefinition);
    }

    public function testCanOverrideRootDefinition(): void
    {
        $configDefinition = new class extends BaseConfigDefinition implements ConfigurationInterface
        {
            protected function getRootDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
            {
                $rootNode = parent::getRootDefinition($treeBuilder);
                $rootNode
                    ->children()
                    ->scalarNode('requiredRootNode')
                    ->isRequired()
                    ->cannotBeEmpty();
                return $rootNode;
            }
        };
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "requiredRootNode" at path "root" must be configured.');

        new BaseConfig([], $configDefinition);
    }

    public function testIsForwardCompatible(): void
    {
        $config = new BaseConfig(['yetNonexistentKey' => 'value']);
        $this->assertSame(['yetNonexistentKey' => 'value'], $config->getData());
    }

    public function testGettersWillNotFailIfKeyIsMissing(): void
    {
        $config = new BaseConfig([
            'lorem' => [
                'ipsum' => [
                    'dolores' => 'value',
                ],
            ],
        ]);
        $this->assertSame([], $config->getParameters());
        $this->assertSame('', $config->getAction());
        $this->assertSame([], $config->getAuthorization());
        $this->assertSame('', $config->getOAuthApiAppKey());
        $this->assertSame('', $config->getOAuthApiAppSecret());
        $this->assertSame('', $config->getOAuthApiData());
        $this->assertSame([], $config->getImageParameters());
        $this->assertSame([], $config->getStorage());
        $this->assertSame('', $config->getValue(['parameters', 'ipsum', 'dolor'], ''));
    }

    public function testGettersWillGetKeyIfPresent(): void
    {
        $configDefinition = new class extends BaseConfigDefinition implements ConfigurationInterface
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $nodeDefinition = parent::getParametersDefinition();
                // @formatter:off
                $nodeDefinition->isRequired();
                $nodeDefinition
                    ->children()
                    ->arrayNode('ipsum')
                        ->children()
                            ->scalarNode('dolor');
                // @formatter:on
                return $nodeDefinition;
            }
        };
        $config = new BaseConfig([
            'parameters' => [
                'ipsum' => [
                    'dolor' => 'value',
                ],
            ],
            'action' => 'run',
            'authorization' => [
                'oauth_api' => [
                    'credentials' => [
                        '#data' => 'value',
                        '#appSecret' => 'secret',
                        'appKey' => 'key',
                    ],
                ],
            ],
            'image_parameters' => ['param1' => 'value1'],
            'storage' => [
                'input' => [
                    'tables' => [],
                ],
                'output' => [
                    'files' => [],
                ],
            ],
        ], $configDefinition);
        $this->assertEquals(
            [
                'ipsum' => [
                    'dolor' => 'value',
                ],
            ],
            $config->getParameters()
        );
        $this->assertEquals(
            'run',
            $config->getAction()
        );
        $this->assertEquals(
            [
                'oauth_api' => [
                    'credentials' => [
                        '#data' => 'value',
                        '#appSecret' => 'secret',
                        'appKey' => 'key',
                    ],
                ],
            ],
            $config->getAuthorization()
        );
        $this->assertEquals(
            'value',
            $config->getOAuthApiData()
        );
        $this->assertEquals(
            'secret',
            $config->getOAuthApiAppSecret()
        );
        $this->assertEquals(
            'key',
            $config->getOAuthApiAppKey()
        );
        $this->assertEquals(
            ['param1' => 'value1'],
            $config->getImageParameters()
        );
        $this->assertEquals(
            [
                'input' => [
                    'tables' => [],
                ],
                'output' => [
                    'files' => [],
                ],
            ],
            $config->getStorage()
        );
        $this->assertEquals(
            'value',
            $config->getValue(['parameters', 'ipsum', 'dolor'])
        );
    }
}
