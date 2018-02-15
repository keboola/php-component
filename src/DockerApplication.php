<?php declare(strict_types = 1);

namespace Keboola\DockerApplication;

use ErrorException;
use Keboola\DockerApplication\Config\Config;
use Keboola\DockerApplication\Config\ConfigDefinition;
use Keboola\DockerApplication\Manifest\ManifestManager;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function strtr;

class DockerApplication
{
    /** @var Config */
    private $config;

    /** @var string */
    private $dataDir;

    /** @var ManifestManager */
    private $manifestManager;

    public function __construct(
        ?string $dataDir = null,
        ?Config $config = null,
        ?ManifestManager $manifestManager = null
    ) {
        $this->setEnvironment();
        if (!$dataDir) {
            $dataDir = getenv('KBC_DATADIR') === false ? '/data/' : (string)getenv('KBC_DATADIR');
        }
        $this->setDataDir($dataDir);

        if (!$config) {
            $this->loadConfig();
        }
        $this->config = $config;

        if (!$manifestManager) {
            $manifestManager = new ManifestManager($dataDir);
        }
        $this->manifestManager = $manifestManager;
    }

    public function setEnvironment(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext): bool {
            if (!(error_reporting() & $errno)) {
                // respect error_reporting() level
                // libraries used in custom components may emit notices that cannot be fixed
                return false;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    protected function loadConfig(): Config
    {
        $jsonContents = file_get_contents($this->dataDir . 'config.json');
        $jsonEncoder = new JsonEncoder();
        return new Config($jsonEncoder->decode($jsonContents, JsonEncoder::FORMAT), $this->getConfigDefinition());
    }

    protected function getConfigDefinition(): ConfigurationInterface
    {
        return new ConfigDefinition();
    }

    public function setDataDir(string $dataDir): void
    {
        // make sure it's platform independent
        $dataDir = strtr($dataDir, [DIRECTORY_SEPARATOR => '/']);

        $this->dataDir = rtrim($dataDir, '/') . '/';
    }

    /**
     * @return mixed
     */
    public function getExpectedOutputFiles()
    {
        // TODO: Implement getExpectedOutputFiles() method.
    }

    /**
     * @return mixed[]
     */
    public function getInputTables()
    {
        return $this->getConfig()->getValueOrNull('storage', 'input', 'tables');
    }

    /**
     * @param string $tableName
     * @return mixed
     */
    public function getTableManifest(string $tableName)
    {
        // TODO: Implement getTableManifest() method.
    }

    /**
     * @return mixed[]
     */
    public function getExpectedOutputTables()
    {
        // TODO: Implement getExpectedOutputTables() method.
    }

    public function getDataDir(): string
    {
        return $this->dataDir;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getManifestManager(): ManifestManager
    {
        return $this->manifestManager;
    }
}
