<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\FilesystemUtils;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use const DIRECTORY_SEPARATOR;
use function array_shift;
use function array_unshift;
use function implode;
use function rtrim;
use function sprintf;
use function strlen;

class FilesystemUtils
{
    /**
     * True if path is relative or
     *
     * @param string $path
     * @param string $directory
     * @return string
     */
    public static function isPathInDirectory(string $path, string $directory): bool
    {
        // normalize
        $directory = self::addTrailingSlash(self::platformIndependentPath($directory));
        $path = self::addTrailingSlash(self::platformIndependentPath($path));

        return strpos($path, $directory) === 0;
    }

    public static function pathFromSegments(string ...$segments): string
    {
        if (count($segments) === 0) {
            return '';
        }

        $firstSegment = self::platformIndependentPath(array_shift($segments));

        $segments = array_map(function ($segment) {
            $segment = self::platformIndependentPath($segment);
            return ltrim($segment, '/');
        }, $segments);

        array_unshift($segments, $firstSegment);

        return implode('/', $segments);
    }

    public static function platformIndependentPath(string $path): string
    {
        return strtr($path, [DIRECTORY_SEPARATOR => '/']);
    }

    public static function addTrailingSlash(string $path): string
    {
        return rtrim($path, '/\\') . '/';
    }
}
