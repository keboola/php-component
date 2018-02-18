<?php declare(strict_types = 1);

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir = __DIR__ . '/src')
;

/*$versions = GitVersionCollection::create($dir)
//    ->addFromTags('v2.0.*')
//    ->add('2.0', '2.0 branch')
    ->add('master', 'master branch')
;*/

return new Sami($iterator, array(
    'theme'                => 'github',
    //'versions'             => $versions,
    'title'                => 'php-docker-application',
    'build_dir'            => __DIR__ . '/docs/',
    'cache_dir'            => __DIR__ . '/cache/',
    // use a custom theme directory
    'template_dirs'        => array(__DIR__.'/vendor/devedge/sami-github/'),
    'default_opened_level' => 2,
));
