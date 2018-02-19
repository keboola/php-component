<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Manifest;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use const PATHINFO_EXTENSION;
use function file_get_contents;
use function file_put_contents;
use function pathinfo;

class ManifestManager
{
    /** @var string */
    private $dataDir;

    public function __construct(
        string $dataDir
    ) {
        $this->dataDir = $dataDir;
    }

    final public static function getManifestFilename(string $fileName): string
    {
        $isAlreadyManifestFilename = pathinfo($fileName, PATHINFO_EXTENSION) === 'manifest';
        if ($isAlreadyManifestFilename) {
            return $fileName;
        }
        return $fileName . '.manifest';
    }

    /**
     * @param string $fileName
     * @param string[] $fileTags
     * @param bool $isPublic
     * @param bool $isPermanent
     * @param bool $notify
     */
    public function writeFileManifest(
        string $fileName,
        array $fileTags = [],
        bool $isPublic = false,
        bool $isPermanent = true,
        bool $notify = false
    ): void {
        $manifestName = self::getManifestFilename($fileName);
        $manifest = [
            'is_permanent' => $isPermanent,
            'is_public' => $isPublic,
            'tags' => $fileTags,
            'notify' => $notify,
        ];
        $encoder = new JsonEncoder();
        $manifestJson = $encoder->encode($manifest, JsonEncoder::FORMAT);

        file_put_contents($manifestName, $manifestJson . "\n");
    }

    /**
     * @param string $fileName
     * @param string $destination
     * @param string[] $primaryKeyColumns
     */
    public function writeTableManifest(
        string $fileName,
        string $destination,
        array $primaryKeyColumns = []
    ): void {
        $manifestName = self::getManifestFilename($fileName);
        $manifest = [
            'destination' => $destination,
            'primary_key' => $primaryKeyColumns,
        ];
        $encoder = new JsonEncoder();
        $manifestJson = $encoder->encode($manifest, JsonEncoder::FORMAT);

        file_put_contents($manifestName, $manifestJson . "\n");
    }

    /**
     * @param string $fileName
     * @return mixed[]
     */
    public function getFileManifest(string $fileName): array
    {
        $baseDir = implode('/', [$this->dataDir, 'in', 'files']);
        return $this->loadManifest($fileName, $baseDir);
    }

    /**
     * @param string $tableName
     * @return mixed
     */
    public function getTableManifest(string $tableName)
    {
        $baseDir = implode('/', [$this->dataDir, 'in', 'tables']);

        return $this->loadManifest($tableName, $baseDir);
    }

    /**
     * @param string $fileName
     * @param string $baseDir
     * @return mixed[]
     */
    private function loadManifest(string $fileName, string $baseDir): array
    {
        $isPathInDirectory = strpos($fileName, $baseDir) === 0;
        if (!$isPathInDirectory) {
            $fs = new Filesystem();
            if ($fs->isAbsolutePath($fileName)) {
                throw new InvalidArgumentException(sprintf(
                    'Manifest source "%s" must be in the data directory (%s)!',
                    $fileName,
                    $baseDir
                ));
            }

            $fileName = implode('/', [$baseDir, $fileName]);
        }

        $decoder = new JsonEncoder();
        return $decoder->decode(file_get_contents(self::getManifestFilename($fileName)), JsonEncoder::FORMAT);
    }
}
