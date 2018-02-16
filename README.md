PHP Docker application
-----------------

[![Build Status](https://travis-ci.org/keboola/php-docker-application.svg?branch=master)](https://travis-ci.org/keboola/php-docker-application)
[![Code Climate](https://codeclimate.com/github/keboola/php-docker-application/badges/gpa.svg)](https://codeclimate.com/github/keboola/php-docker-application)

General library for php application running in KBC. The library provides function related to [Docker Runner](https://github.com/keboola/docker-bundle).

Installation
===============

```
composer install keboola/php-docker-application
```

Usage
============

Create a subclass of `DockerApplication`. 

```php
<?php declare(strict_types = 1);
// src/Application.php
use Keboola\DockerApplication\DockerApplication;

class Application extends DockerApplication
{
    public function run(): void
    {
        // your custom code
    }
}

```

Use this `run.php` template. 

```php
<?php
// src/run.php

require_once "vendor/autoload.php";

try {
    $app = new Application();
    $app->run();
    exit(0);
} catch (MyComponent\Exception\UserException $e) {
    echo $e->getMessage();
    exit(1);
} catch(\Throwable $e) {
    echo $e->getMessage();
    echo "errFile:" . $e->getFile();
    echo "errLine:" . $e->getLine();
    echo "code:" . $e->getCode();
    echo "trace :";
    var_export($e->getTrace())
    exit(2);
}
```

See documentation [in doc directory](https://github.com/keboola/python-docker-application/tree/master/doc) for full list of available functions. See [development guide](http://developers.keboola.com/extend/custom-science/python/) for help with KBC integration.
