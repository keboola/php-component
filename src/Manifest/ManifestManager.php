<?php declare(strict_types = 1);

namespace Keboola\Component\Manifest;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use const PATHINFO_EXTENSION;
use function file_get_contents;
use function pathinfo;

/**
 * Handles everything related to generating and reading manifests for tables and files.
 */
class ManifestManager
{
    /** @var string */
    private $dataDir;

    public function __construct(
        string $dataDir
    ) {
        $this->dataDir = $dataDir;
    }

    final public function getManifestFilename(string $fileName): string
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
     * @param bool $isEncrypted
     */
    public function writeFileManifest(
        string $fileName,
        array $fileTags = [],
        bool $isPublic = false,
        bool $isPermanent = true,
        bool $notify = false,
        bool $isEncrypted = false
    ): void {
        $manifestName = $this->getManifestFilename($fileName);
        $manifest = [
            'is_permanent' => $isPermanent,
            'is_public' => $isPublic,
            'tags' => $fileTags,
            'notify' => $notify,
            'is_encrypted' => $isEncrypted,
        ];
        $this->internalWriteManifest($manifestName, $manifest);
    }

    /**
     * @param string $fileName
     * @param string $destination
     * @param string[] $primaryKeyColumns
     * @param string[] $columns
     * @param bool $incremental
     * @param mixed[][] $metadata
     * @param mixed[][] $columnMetadata
     * @param string $delimiter
     * @param string $enclosure
     */
    public function writeTableManifest(
        string $fileName,
        string $destination = '',
        array $primaryKeyColumns = [],
        array $columns = [],
        bool $incremental = false,
        array $metadata = [],
        array $columnMetadata = [],
        string $delimiter = ',',
        string $enclosure = '"'
    ): void {
        $manifestName = $this->getManifestFilename($fileName);
        $manifest = [
            'destination' => $destination,
            'primary_key' => $primaryKeyColumns,
            'delimiter' => $delimiter,
            'enclosure' => $enclosure,
            'columns' => $columns,
            'incremental' => $incremental,
            'metadata' => $metadata,
            'column_metadata' => $columnMetadata,
        ];
        $this->internalWriteManifest($manifestName, $manifest);
    }

    /**
     * @param string $filename
     * @param mixed[] $manifest
     */
    public function writeTableManifestFromArray(
        string $filename,
        array $manifest
    ): void {
        $this->writeTableManifest(
            $filename,
            $manifest['destination'] ?? '',
            $manifest['primary_key'] ?? [],
            $manifest['columns'] ?? [],
            $manifest['incremental'] ?? false,
            $manifest['metadata'] ?? [],
            $manifest['column_metadata'] ?? [],
            $manifest['delimiter'] ?? ',',
            $manifest['enclosure'] ?? '"'
        );
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
        return $decoder->decode(file_get_contents($this->getManifestFilename($fileName)), JsonEncoder::FORMAT);
    }

    /**
     * @param string $manifestAbsolutePath
     * @param mixed[] $manifestContents
     */
    private function internalWriteManifest(string $manifestAbsolutePath, array $manifestContents): void
    {
        $encoder = new JsonEncoder();
        $manifestJson = $encoder->encode($manifestContents, JsonEncoder::FORMAT);
        $filesystem = new Filesystem();
        $filesystem->dumpFile($manifestAbsolutePath, $manifestJson . "\n");
    }
}
