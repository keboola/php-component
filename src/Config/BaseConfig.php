<?php

declare(strict_types=1);

namespace Keboola\Component\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use function array_key_exists;
use function implode;

/**
 * Offers basic abstraction over the JSON config. You can extend it and add your own getters for custom parameters.
 * It's then easier to use them then to remember under which key they are in the parameters array.
 */
class BaseConfig implements ConfigInterface
{
    /** @var mixed[] */
    protected array $config;

    private ConfigurationInterface $configDefinition;

    /**
     * @param mixed[] $config Configuration array
     * @param null|ConfigurationInterface $configDefinition (optional) Custom class to validate the config
     */
    public function __construct(
        array $config,
        ?ConfigurationInterface $configDefinition = null
    ) {
        $this->setConfigDefinition($configDefinition);
        $this->setConfig($config);
    }

    /**
     * @param mixed[] $config
     */
    private function setConfig(array $config): void
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($this->configDefinition, [$config]);
        $this->config = $processedConfig;
    }

    private function setConfigDefinition(?ConfigurationInterface $configDefinition): void
    {
        if ($configDefinition === null) {
            $configDefinition = new BaseConfigDefinition();
        }
        $this->configDefinition = $configDefinition;
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
     * Returns value by key. You can supply default value for when the key is missing.
     * Without default value exception is thrown for nonexistent keys.
     *
     * @param string[] $keys
     */
    public function getValue(array $keys, mixed $default = null): mixed
    {
        $config = $this->config;
        $pointer = &$config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $pointer)) {
                if ($default === null) {
                    throw new InvalidArgumentException(sprintf(
                        'Key "%s" does not exist',
                        implode('.', $keys)
                    ));
                }
                return $default;
            }
            $pointer = &$pointer[$key];
        }
        return $pointer;
    }

    /**
     * Returns `parameters` section of the config
     */
    public function getParameters(): array
    {
        return (array) $this->getValue(['parameters'], []);
    }

    /**
     * Returns `storage` section of the config
     */
    public function getStorage(): array
    {
        return (array) $this->getValue(['storage'], []);
    }

    /**
     * Returns `image_parameters` section of the config
     */
    public function getImageParameters(): array
    {
        return (array) $this->getValue(['image_parameters'], []);
    }

    /**
     * Returns `authorization` section of the config
     */
    public function getAuthorization(): array
    {
        return (array) $this->getValue(['authorization'], []);
    }

    /**
     * Returns `action` section of the config
     */
    public function getAction(): string
    {
        return strval($this->getValue(['action'], 'run'));
    }

    public function getInputFiles(): array
    {
        return (array) $this->getValue(['storage', 'input', 'files'], []);
    }

    public function getExpectedOutputFiles(): array
    {
        return (array) $this->getValue(['storage', 'output', 'files'], []);
    }

    public function getInputTables(): array
    {
        return (array) $this->getValue(['storage', 'input', 'tables'], []);
    }

    public function getExpectedOutputTables(): array
    {
        return (array) $this->getValue(['storage', 'output', 'tables'], []);
    }

    public function getOAuthApiData(): mixed
    {
        return $this->getValue(['authorization', 'oauth_api', 'credentials', '#data'], '');
    }

    public function getOAuthApiAppSecret(): string
    {
        return strval($this->getValue(['authorization', 'oauth_api', 'credentials', '#appSecret'], ''));
    }

    public function getOAuthApiAppKey(): string
    {
        return strval($this->getValue(['authorization', 'oauth_api', 'credentials', 'appKey'], ''));
    }
}
