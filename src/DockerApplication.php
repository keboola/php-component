<?php declare(strict_types = 1);

namespace Keboola\DockerApplication;

use ErrorException;
use Keboola\DockerApplication\Config\Config;
use Keboola\DockerApplication\Config\ConfigDefinition;
use Keboola\DockerApplication\Manifest\ManifestManager;
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
        ?string $dataDir = null
    ) {
        $this->setEnvironment();

        if (!$dataDir) {
            $dataDir = getenv('KBC_DATADIR') === false ? '/data/' : (string)getenv('KBC_DATADIR');
        }
        $this->setDataDir($dataDir);
        $this->loadConfig();
        $this->setManifestManager($dataDir);
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
        $configClass = $this->getConfigClass();
        $configDefinitionClass = $this->getConfigDefinitionClass();
        $this->config = new $configClass(
            $jsonEncoder->decode($jsonContents, JsonEncoder::FORMAT),
            new $configDefinitionClass()
        );
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    public function setDataDir(string $dataDir): void
    {
        // make sure it's platform independent
        $dataDir = strtr($dataDir, [DIRECTORY_SEPARATOR => '/']);

        $this->dataDir = rtrim($dataDir, '/') . '/';
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

    public function run(): void
    {
        // to be implemented in subclass
    }

    /**
     * @return mixed
     */
    protected function getConfigClass()
    {
        return Config::class;
    }

    /**
     * @param null|string $dataDir
     */
    protected function setManifestManager(?string $dataDir): void
    {
        $this->manifestManager = new ManifestManager($dataDir);
    }
}
