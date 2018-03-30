<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest;

use InvalidArgumentException;
use Keboola\Component\Manifest\ManifestManager\Options\WriteTableManifestOptions;
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
        $tableManifestName = $this->getManifestFilename($fileName);
        $manifest = [
            'is_permanent' => $isPermanent,
            'is_public' => $isPublic,
            'tags' => $fileTags,
            'notify' => $notify,
            'is_encrypted' => $isEncrypted,
        ];
        $this->internalWriteFileManifest($tableManifestName, $manifest);
    }

    public function writeTableManifestFromOptions(string $fileName, WriteTableManifestOptions $options): void
    {
        $manifestName = self::getManifestFilename($fileName);

        $this->internalWriteTableManifest($manifestName, $options->toArray());
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

    /**
     * @param string $tableManifestName
     * @param mixed[] $manifestContents
     */
    private function internalWriteTableManifest(string $tableManifestName, array $manifestContents): void
    {
        $this->internalWriteManifest($this->dataDir . '/out/tables/' . $tableManifestName, $manifestContents);
    }

    /**
     * @param string $fileManifestName
     * @param mixed[] $manifestContents
     */
    private function internalWriteFileManifest(string $fileManifestName, array $manifestContents): void
    {
        $this->internalWriteManifest($this->dataDir . '/out/files/' . $fileManifestName, $manifestContents);
    }
}
