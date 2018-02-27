<?php declare(strict_types = 1);

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir = __DIR__ . '/src');

return new Sami($iterator, [
    'title' => 'php-docker-application',
    'build_dir' => __DIR__ . '/docs/',
    'cache_dir' => __DIR__ . '/cache/',
    'default_opened_level' => 2,
]);
