<?php declare(strict_types=1);

namespace Keboola\DockerApplication;

interface DockerApplicationInterface
{
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
    ): void;

    /**
     * @param string $fileName
     * @param string $destination
     * @param string[] $primaryKeyColumns
     */
    public static function writeTableManifest(
        string $fileName,
        string $destination,
        array $primaryKeyColumns = []
    ): void;

    /**
     * @return mixed[]
     */
    public function getParameters(): array;

    public function getAction(): string;

    /**
     * @return string[]
     */
    public function getAuthorization(): array;

    /**
     * @return string[]
     */
    public function getInputFiles(): array;

    /**
     * @param string $fileName
     * @return string[]
     */
    public function getFileManifest(string $fileName): array;

    /**
     * @return string[][]
     */
    public function getExpectedOutputFiles(): array;

    /**
     * @return string[][]
     */
    public function getInputTables(): array;

    /**
     * @param string $tableName
     * @return string[]
     */
    public function getTableManifest(string $tableName): array;

    /**
     * @return string[][]
     */
    public function getExpectedOutputTables(): array;

    public function getDataDir(): string;

    /**
     * @return string[]
     */
    public function getOAuthApiData(): array;

    public function getOAuthApiAppSecret(): string;

    public function getOAuthApiAppKey(): string;
}
