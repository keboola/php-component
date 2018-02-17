<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use function array_key_exists;
use function implode;

class Config implements ConfigInterface
{
    /** @var mixed[] */
    protected $config;

    /** @var null|ConfigurationInterface */
    private $configDefinition;

    /**
     * @param mixed[] $config Configuration array
     * @param null|ConfigurationInterface $configDefinition (optional) Custom class to validate the config
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
     * Returns all the data in config as associative array
     *
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->config;
    }

    /**
     * Returns value by key or null if the value is not present in the config
     *
     * @param string[] $keys key is specified as array, so `some.key` would be `['some', 'key']`
     * @return mixed
     */
    public function getValueOrNull(array $keys)
    {
        try {
            return $this->getValue($keys);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Returns value by key. If value is not present exception is thrown.
     *
     * @param string[] $keys
     * @return mixed
     */
    public function getValue(array $keys)
    {
        $config = $this->config;
        $pointer = &$config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $pointer)) {
                throw new InvalidArgumentException(sprintf(
                    'Key "%s" does not exist',
                    implode('.', $keys)
                ));
            }
            $pointer = &$pointer[$key];
        }
        return $pointer;
    }

    /**
     * Returns `parameters` section of the config
     *
     * @return mixed|null
     */
    public function getParameters()
    {
        return $this->getValueOrNull(['parameters']);
    }

    /**
     * Returns `storage` section of the config
     *
     * @return mixed|null
     */
    public function getStorage()
    {
        return $this->getValueOrNull(['storage']);
    }

    /**
     * Returns `image_parameters` section of the config
     *
     * @return mixed|null
     */
    public function getImageParameters()
    {
        return $this->getValueOrNull(['image_parameters']);
    }

    /**
     * Returns `authorization` section of the config
     *
     * @return mixed|null
     */
    public function getAuthorization()
    {
        return $this->getValueOrNull(['authorization']);
    }

    /**
     * Returns `action` section of the config
     *
     * @return mixed|null
     */
    public function getAction()
    {
        return $this->getValueOrNull(['action']);
    }

    /**
     * @return mixed[][]
     */
    public function getInputFiles(): array
    {
        $files = $this->getValueOrNull(['storage', 'input', 'files']);
        if ($files === null) {
            return [];
        }
        return $files;
    }

    /**
     * @return mixed[][]
     */
    public function getExpectedOutputFiles(): array
    {
        $files = $this->getValueOrNull(['storage', 'output', 'files']);
        if ($files === null) {
            return [];
        }
        return $files;
    }

    /**
     * @return mixed[][]
     */
    public function getInputTables(): array
    {
        $tables = $this->getValueOrNull(['storage', 'input', 'tables']);
        if ($tables === null) {
            return [];
        }
        return $tables;
    }

    /**
     * @return mixed[][]
     */
    public function getExpectedOutputTables(): array
    {
        $tables = $this->getValueOrNull(['storage', 'output', 'tables']);
        if ($tables === null) {
            return [];
        }
        return $tables;
    }

    /**
     * @return mixed
     */
    public function getOAuthApiData()
    {
        return $this->getValueOrNull(['oauth_api', 'credentials', '#data']);
    }

    public function getOAuthApiAppSecret(): string
    {
        return $this->getValueOrNull(['oauth_api', 'credentials', '#appSecret']);
    }

    public function getOAuthApiAppKey(): string
    {
        return $this->getValueOrNull(['oauth_api', 'credentials', 'appKey']);
    }
}
