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
    public function getData(): array
    {
        return $this->config;
    }

    /**
     * @param string[] $keys
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
     * @return mixed|null
     */
    public function getParameters()
    {
        return $this->getValueOrNull(['parameters']);
    }

    /**
     * @return mixed|null
     */
    public function getStorage()
    {
        return $this->getValueOrNull(['storage']);
    }

    /**
     * @return mixed|null
     */
    public function getImageParameters()
    {
        return $this->getValueOrNull(['image_parameters']);
    }

    /**
     * @return mixed|null
     */
    public function getAuthorization()
    {
        return $this->getValueOrNull(['authorization']);
    }

    /**
     * @return mixed|null
     */
    public function getAction()
    {
        return $this->getValueOrNull(['action']);
    }

    /**
     * @return mixed[]
     */
    public function getInputFiles()
    {
        return $this->getValueOrNull(['storage', 'input', 'files']);
    }

    /**
     * @return mixed
     */
    public function getExpectedOutputFiles()
    {
        return $this->getValueOrNull(['storage', 'output', 'files']);
    }

    /**
     * @return mixed[]
     */
    public function getInputTables()
    {
        return $this->getValueOrNull(['storage', 'input', 'tables']);
    }

    /**
     * @return mixed[]
     */
    public function getExpectedOutputTables()
    {
        return $this->getValueOrNull(['storage', 'output', 'tables']);
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
