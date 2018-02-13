<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use function array_key_exists;

class Config implements ConfigInterface
{
    /** @var mixed[] */
    protected $config;

    /** @var null|ConfigurationInterface */
    private $configDefinition;

    /**
     * @param mixed[] $config
     */
    public function __construct(
        array $config,
        ?ConfigurationInterface $configDefinition = null
    ) {
        $this->configDefinition = $configDefinition;
        $this->setConfig($config);
    }

    /**
     * @param mixed[] $config
     */
    private function setConfig(array $config): void
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($this->getConfigDefinition(), [$config]);
        $this->config = $processedConfig;
    }

    private function getConfigDefinition(): ConfigurationInterface
    {
        if ($this->configDefinition === null) {
            $this->configDefinition = new ConfigDefinition();
        }
        return $this->configDefinition;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        if (!array_key_exists('parameters', $this->config)) {
            return [];
        }
        return $this->config['parameters'];
    }

    /**
     * @return mixed[]
     */
    public function getStorage(): array
    {
        if (!array_key_exists('storage', $this->config)) {
            return [];
        }
        return $this->config['storage'];
    }

    /**
     * @return mixed[]
     */
    public function getImageParameters(): array
    {
        if (!array_key_exists('image_parameters', $this->config)) {
            return [];
        }
        return $this->config['image_parameters'];
    }

    /**
     * @return mixed[]
     */
    public function getAuthorization(): array
    {
        if (!array_key_exists('authorization', $this->config)) {
            return [];
        }
        return $this->config['authorization'];
    }

    /**
     * @return mixed[]
     */
    public function getAction(): array
    {
        if (!array_key_exists('action', $this->config)) {
            return [];
        }
        return $this->config['action'];
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->config;
    }
}
