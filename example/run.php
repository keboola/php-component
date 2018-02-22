<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = new MyComponent\Application();
    $app->run();
    exit(0);
} catch (\Keboola\DockerApplication\UserException $e) {
    echo $e->getMessage();
    exit(1);
}
