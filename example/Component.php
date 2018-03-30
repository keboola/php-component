<?php

declare(strict_types=1);

namespace MyComponent;

class Component extends \Keboola\Component\BaseComponent
{
    public function run(): void
    {
        // get parameters
        $parameters = $this->getConfig()->getParameters();

        // get value of customKey.customSubkey parameter and fail if missing
        $customParameter = $this->getConfig()->getValue(['parameters', 'customKey', 'customSubkey']);

        // get value with default value if not present
        $customParameterOrNull = $this->getConfig()->getValue(['parameters', 'customKey'], 'someDefaultValue');

        // get manifest for input file
        $fileManifest = $this->getManifestManager()->getFileManifest('input-file.csv');

        // get manifest for input table
        $tableManifest = $this->getManifestManager()->getTableManifest('in.tableName');

        // write manifest for output file
        $this->getManifestManager()->writeFileManifest('out-file.csv', ['tag1', 'tag2']);

        // write manifest for output table
        $this->getManifestManager()->writeTableManifest('data.csv', 'out.report', ['id']);
    }
}
