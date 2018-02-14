<?php declare(strict_types = 1);

namespace Keboola\DockerApplication;

use Keboola\DockerApplication\Config\Config;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function realpath;
use function strtr;

class DockerApplication implements DockerApplicationInterface
{
    /** @var Config */
    private $config;

    /** @var string */
    private $dataDir;

    public function setDataDir(string $dataDir): void
    {
        // make sure it's platform independent
        $dataDir = strtr($dataDir, [DIRECTORY_SEPARATOR => '/']);

        $this->dataDir = rtrim($dataDir, '/') . '/';
    }

    public function __construct(
        string $dataDir
    ) {
        $this->setDataDir($dataDir);
    }

    public function getConfig(): Config
    {
        if ($this->config === null) {
            $jsonContents = file_get_contents($this->getDataDir() . 'config.json');
            $jsonEncoder = new JsonEncoder();
            $this->config = new Config($jsonEncoder->decode($jsonContents, JsonEncoder::FORMAT));
        }
        return $this->config;
    }

    /**
     * @param string $fileName
     * @param string[] $fileTags
     * @param bool $isPublic
     * @param bool $isPermanent
     * @param bool $notify
     */
    public static function writeFileManifest(
        string $fileName,
        array $fileTags = [],
        bool $isPublic = false,
        bool $isPermanent = true,
        bool $notify = false
    ): void {
        // TODO: Implement writeFileManifest() method.
    }

    /**
     * @param string $fileName
     * @param string $destination
     * @param string[] $primaryKeyColumns
     */
    public static function writeTableManifest(
        string $fileName,
        string $destination,
        array $primaryKeyColumns = []
    ): void {
        // TODO: Implement writeTableManifest() method.
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->getConfig()->getParameters();
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->getConfig()->getAction();
    }

    /**
     * @return mixed
     */
    public function getAuthorization()
    {
        return $this->getConfig()->getAuthorization();
    }

    /**
     * @return mixed
     */
    public function getInputFiles()
    {
        return $this->getConfig()->getKey('storage', 'input', 'files');
    }

    /**
     * @param string $fileName
     * @return mixed
     */
    public function getFileManifest(string $fileName)
    {
        $fileName = realpath($fileName);
        $baseDir =

            file_name = os . path . normpath(file_name)
        base_dir = os . path . normpath(os . path . join(self . data_dir, 'in', 'files'))
        if file_name[0:len(base_dir)] != base_dir:
            file_name = os . path . join(base_dir, file_name)

        with open(file_name + '.manifest') as manifest_file:
            manifest = json . load(manifest_file)
        return manifest
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
        return $this->getConfig()->getKey('storage', 'input', 'tables');
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

    /**
     * @return mixed
     */
    public function getOAuthApiData()
    {
        // TODO: Implement getOAuthApiData() method.
    }

    public function getOAuthApiAppSecret(): string
    {
        // TODO: Implement getOAuthApiAppSecret() method.
    }

    public function getOAuthApiAppKey(): string
    {
        // TODO: Implement getOAuthApiAppKey() method.
    }
}
