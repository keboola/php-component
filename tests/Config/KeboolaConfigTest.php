<?php declare(strict_types = 1);

namespace Keboola\Component\Tests\Config;

use Keboola\Component\Config\KeboolaConfig;
use Keboola\Component\Config\KeboolaConfigDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class KeboolaConfigTest extends TestCase
{
    public function testWillCreateConfigFromArray(): void
    {
        $config = new KeboolaConfig([]);

        $this->assertInstanceOf(KeboolaConfig::class, $config);
    }

    public function testCanOverrideParametersDefinition(): void
    {
        $configDefinition = new class extends KeboolaConfigDefinition implements ConfigurationInterface
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

        $config = new KeboolaConfig(['parameters' => []], $configDefinition);
    }

    public function testCanOverrideRootDefinition(): void
    {
        $configDefinition = new class extends KeboolaConfigDefinition implements ConfigurationInterface
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

        $config = new KeboolaConfig([], $configDefinition);
    }

    public function testIsForwardCompatible(): void
    {
        $config = new KeboolaConfig(['yetNonexistentKey' => 'value']);
        $this->assertSame(['yetNonexistentKey' => 'value'], $config->getData());
    }

    public function testGettersWillNotFailIfKeyIsMissing(): void
    {
        $config = new KeboolaConfig([
            'lorem' => [
                'ipsum' => [
                    'dolores' => 'value',
                ],
            ],
        ]);
        $this->assertSame([], $config->getParameters());
        $this->assertSame('', $config->getAction());
        $this->assertSame([], $config->getAuthorization());
        $this->assertSame([], $config->getImageParameters());
        $this->assertSame([], $config->getStorage());
        $this->assertSame('', $config->getValue(['parameters', 'ipsum', 'dolor'], ''));
    }

    public function testGettersWillGetKeyIfPresent(): void
    {
        $config = new KeboolaConfig([
            'parameters' => [
                'ipsum' => [
                    'dolor' => 'value',
                ],
            ],
            'action' => 'run',
            'authorization' => [
                '#secret' => 'x',
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
        ]);
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
                '#secret' => 'x',
            ],
            $config->getAuthorization()
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
