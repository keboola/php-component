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
     * @return mixed|null
     */
    public function getParameters()
    {
        return $this->getKey('parameters');
    }

    /**
     * @return mixed|null
     */
    public function getStorage()
    {
        return $this->getKey('storage');
    }

    /**
     * @return mixed|null
     */
    public function getImageParameters()
    {
        return $this->getKey('image_parameters');
    }

    /**
     * @return mixed|null
     */
    public function getAuthorization()
    {
        return $this->getKey('authorization');
    }

    /**
     * @return mixed|null
     */
    public function getAction()
    {
        return $this->getKey('action');
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->config;
    }

    /**
     * @param string ...$keys
     * @return mixed
     */
    public function getKey(string ...$keys)
    {
        $config = $this->config;
        $pointer = &$config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $pointer)) {
                return null;
            }
            $pointer = &$pointer[$key];
        }
        return $pointer;
    }
}
