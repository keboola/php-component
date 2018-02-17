<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Tests\FilesystemUtils;

use Keboola\DockerApplication\FilesystemUtils\FilesystemUtils;
use PHPUnit\Framework\TestCase;

class FilesystemUtilsTest extends TestCase
{
    /**
     * @dataProvider providePathsAndDirectoriesForIsPathInDirectory
     */
    public function testIsPathInDirectory(bool $expected, string $path, string $directory): void
    {
        $this->assertSame(
            $expected,
            FilesystemUtils::isPathInDirectory($path, $directory)
        );
    }

    /**
     * @return string[][]
     */
    public function providePathsAndDirectoriesForIsPathInDirectory(): array
    {
        return [
            'is in directory' => [
                true,
                '/data/out/file/test.csv',
                '/data/out/file/',
            ],
            'is not in directory' => [
                false,
                '/data/out/file/test.csv',
                '/data/out/tables',
            ],
            'is the same as directory' => [
                true,
                '/data/out/file/',
                '/data/out/file/',
            ],
            'relative paths' => [
                true,
                'data/out/file/test.csv',
                'data/out/file/',
            ],
            'further subdirs in path' => [
                true,
                'data/out/file/test.csv',
                'data',
            ],
            'mixed separators' => [
                true,
                '/first\\second/test.csv',
                '/first/second',
            ],
        ];
    }

    /**
     * @dataProvider providePathsForTrailingSlash
     */
    public function testAddTrailingSlash($expected, $path): void
    {
        $this->assertSame(
            $expected,
            FilesystemUtils::addTrailingSlash($path)
        );
    }

    /**
     * @return string[][]
     */
    public function providePathsForTrailingSlash(): array
    {
        return [
            'add missing slash' => [
                '/lorem/ipsum/',
                '/lorem/ipsum',
            ],
            'will not add double slash' => [
                '/lorem/ipsum/',
                '/lorem/ipsum/',
            ],
            'works with windows separators as well' => [
                'c:\\lorem\\ipsum/',
                'c:\\lorem\\ipsum',
            ],
            'empty path' => [
                '/',
                '',
            ],
            'relative path' => [
                './test/',
                './test',
            ],
        ];
    }

    /**
     * @dataProvider providePathSegments
     *
     * @param string $expected
     * @param string[] $segments
     */
    public function testCreatePathFromSegments(string $expected, array $segments): void
    {
        $this->assertSame(
            $expected,
            FilesystemUtils::pathFromSegments(...$segments)
        );
    }

    /**
     * @return mixed[][]
     */
    public function providePathSegments(): array
    {
        return [
            'without trailing slash' => [
                '/some/path',
                [
                    '/some',
                    'path',
                ],
            ],
            'with trailing slash' => [
                '/some/path/',
                [
                    '/some',
                    'path/',
                ],
            ],
            'relative path' => [
                'some/path/',
                [
                    'some',
                    'path/',
                ],
            ],
            'one segment' => [
                '/some',
                [
                    '/some',
                ],
            ],
            'no segments' => [
                '',
                [],
            ],
        ];
    }
}
