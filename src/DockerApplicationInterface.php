<?php declare(strict_types = 1);

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
     * @return mixed
     */
    public function getParameters();

    /**
     * @return mixed
     */
    public function getAction();

    /**
     * @return mixed
     */
    public function getAuthorization();

    /**
     * @return mixed
     */
    public function getInputFiles();

    /**
     * @param string $fileName
     * @return mixed
     */
    public function getFileManifest(string $fileName);

    /**
     * @return mixed
     */
    public function getExpectedOutputFiles();

    /**
     * @return mixed[]
     */
    public function getInputTables();

    /**
     * @param string $tableName
     * @return mixed
     */
    public function getTableManifest(string $tableName);

    /**
     * @return mixed[]
     */
    public function getExpectedOutputTables();

    public function getDataDir(): string;

    /**
     * @return mixed
     */
    public function getOAuthApiData();

    public function getOAuthApiAppSecret(): string;

    public function getOAuthApiAppKey(): string;
}
