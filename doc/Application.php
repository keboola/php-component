<?php declare(strict_types = 1);
// src/Application.php
use Keboola\DockerApplication\DockerApplication;

class Application extends DockerApplication
{
    public function run(): void
    {
        // get parameters
        $parameters = $this->getConfig()->getParameters();

        // get value of customKey.customSubkey parameter and fail if missing
        $customParameter = $this->getConfig()->getValue(['parameters', 'customKey', 'customSubkey']);

        // get value of customKey.customSubkey parameter or null
        $customParameterOrNull = $this->getConfig()->getValueOrNull(['parameters', 'customKey', 'customSubkey']);

        $fileManifest = $this->getManifestManager()->getFileManifest('input-file.csv');
        $tableManifest = $this->getManifestManager()->getTableManifest('in.tableName');
    }
}
