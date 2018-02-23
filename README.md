Keboola PHP Component
-----------------

[![Build Status](https://travis-ci.org/keboola/php-component.svg?branch=master)](https://travis-ci.org/keboola/php-component)
[![Code Climate](https://codeclimate.com/github/keboola/php-component/badges/gpa.svg)](https://codeclimate.com/github/keboola/php-component)

General library for php component running in KBC. The library provides function related to [Docker Runner](https://github.com/keboola/docker-bundle).

Installation
===============

```
composer install keboola/php-component
```

Usage
============

Create a subclass of `KeboolaComponent`. 

```php
<?php declare(strict_types = 1);

namespace MyComponent;

use Keboola\Component\KeboolaComponent;

class Component extends KeboolaComponent
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

```

Use this `src/run.php` template. 

```php
<?php declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = new MyComponent\Component();
    $app->run();
    exit(0);
} catch (\Keboola\Component\UserException $e) {
    echo $e->getMessage();
    exit(1);
} catch (\Throwable $e) {
    echo get_class($e) . ':' . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
    echo "\nCode: " . $e->getCode();
    echo "\nTrace: " . $e->getTraceAsString() . "\n";
    exit(2);
}
```
For more information, please refer to the [generated docs](https://keboola.github.io/php-component/classes.html). See [development guide](https://developers.keboola.com/extend/component/tutorial/) for help with KBC integration.
