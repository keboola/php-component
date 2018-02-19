<?php declare(strict_types = 1);

// src/Application.php
use Keboola\DockerApplication\KeboolaApplication;

class Application extends KeboolaApplication
{
    public function run(): void
    {
        // get parameters
        $parameters = $this->getConfig()->getParameters();

        // get value of customKey.customSubkey parameter and fail if missing
        $customParameter = $this->getConfig()->getValue(['parameters', 'customKey', 'customSubkey']);

        // get value of customKey.customSubkey parameter or null
        $customParameterOrNull = $this->getConfig()->getValueOrNull(['parameters', 'customKey', 'customSubkey']);

        // get manifest for input file
        $fileManifest = $this->getManifestManager()->getFileManifest('input-file.csv');

        // get manifest for input table
        $tableManifest = $this->getManifestManager()->getTableManifest('in.tableName');

        // write manifest for output file
        $this->getManifestManager()->writeFileManifest('out-file.csv', ['tag1', 'tag2']);

        // write manigest for output table
        $this->getManifestManager()->writeTableManifest('data.csv', 'out.report', ['id']);
    }
}