<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\FilesystemUtils;

use const DIRECTORY_SEPARATOR;
use function array_shift;
use function array_unshift;
use function implode;
use function rtrim;

class FilesystemUtils
{
    /**
     * Checks if a given path is inside a given directory
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    public static function isPathInDirectory(string $path, string $directory): bool
    {
        $directory = self::addTrailingSlash(self::platformIndependentPath($directory));
        $path = self::addTrailingSlash(self::platformIndependentPath($path));

        return strpos($path, $directory) === 0;
    }

    /**
     * Works like `os.path.join` in python. Joins multiple path segments,
     * ensuring that there are no double slashes. Returns platform independent
     * path.
     *
     * @param string ...$segments
     * @return string
     */
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

    /**
     * Converts path to platform independent version (with `/` as directory
     * separator).
     *
     * @param string $path
     * @return string
     */
    public static function platformIndependentPath(string $path): string
    {
        return strtr($path, [DIRECTORY_SEPARATOR => '/']);
    }

    /**
     * Ensure that there is a trailing slash, preventing double slash.
     *
     * @param string $path
     * @return string
     */
    public static function addTrailingSlash(string $path): string
    {
        return rtrim($path, '/\\') . '/';
    }
}
