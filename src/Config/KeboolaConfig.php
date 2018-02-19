<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use function array_key_exists;
use function implode;

class KeboolaConfig implements ConfigInterface
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
            $this->configDefinition = new KeboolaConfigDefinition();
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
     * Returns value by key. You can supply default value for when the key is missing.
     * Without default value exception is thrown for nonexistent keys.
     *
     * @param string[] $keys
     * @param null $default
     * @return mixed
     */
    public function getValue(array $keys, $default = null)
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
     *
     * @return mixed[]
     */
    public function getParameters()
    {
        return $this->getValue(['parameters'], []);
    }

    /**
     * Returns `storage` section of the config
     *
     * @return mixed[]
     */
    public function getStorage()
    {
        return $this->getValue(['storage'], []);
    }

    /**
     * Returns `image_parameters` section of the config
     *
     * @return mixed[]
     */
    public function getImageParameters()
    {
        return $this->getValue(['image_parameters'], []);
    }

    /**
     * Returns `authorization` section of the config
     *
     * @return mixed[]
     */
    public function getAuthorization()
    {
        return $this->getValue(['authorization'], []);
    }

    /**
     * Returns `action` section of the config
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->getValue(['action'], '');
    }

    /**
     * @return mixed[][]
     */
    public function getInputFiles(): array
    {
        return $this->getValue(['storage', 'input', 'files'], []);
    }

    /**
     * @return mixed[][]
     */
    public function getExpectedOutputFiles(): array
    {
        return $this->getValue(['storage', 'output', 'files'], []);
    }

    /**
     * @return mixed[][]
     */
    public function getInputTables(): array
    {
        return $this->getValue(['storage', 'input', 'tables'], []);
    }

    /**
     * @return mixed[][]
     */
    public function getExpectedOutputTables(): array
    {
        return $this->getValue(['storage', 'output', 'tables'], []);
    }

    /**
     * @return mixed
     */
    public function getOAuthApiData()
    {
        return $this->getValue(['oauth_api', 'credentials', '#data'], '');
    }

    public function getOAuthApiAppSecret(): string
    {
        return $this->getValue(['oauth_api', 'credentials', '#appSecret'], '');
    }

    public function getOAuthApiAppKey(): string
    {
        return $this->getValue(['oauth_api', 'credentials', 'appKey'], '');
    }
}