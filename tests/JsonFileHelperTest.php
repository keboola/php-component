<?php

declare(strict_types=1);

namespace Keboola\Component\Tests;

use Keboola\Component\JsonFileHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class JsonFileHelperTest extends TestCase
{
    public function testReadNonExistingFileThrowsException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File "/dev/null/file.json" could not be found.');
        JsonFileHelper::read('/dev/null/file.json');
    }

    public function testReadInvalidFileThrowsException(): void
    {
        $this->expectException(NotEncodableValueException::class);
        $this->expectExceptionMessage('Syntax error');
        JsonFileHelper::read(__DIR__ . '/fixtures/json-file-helper-test/invalidJsonFile.json');
    }

    public function testReadFileSuccessfully(): void
    {
        $array = JsonFileHelper::read(__DIR__ . '/fixtures/json-file-helper-test/file.json');
        $this->assertSame(
            [
                'key' => 'value',
                'keys' => ['a', 'b'],
            ],
            $array
        );
    }

    public function testWriteToFileSuccessfully(): void
    {
        $filePath = __DIR__ . '/fixtures/json-file-helper-test/tmp.json';
        $array = [
            'key' => 'val',
            'keys' => [0, 1, 2],
        ];
        JsonFileHelper::write($filePath, $array, false);

        $this->assertSame(
            '{"key":"val","keys":[0,1,2]}',
            file_get_contents($filePath)
        );
    }

    public function testWriteToFilePrettyPrintedSuccessfully(): void
    {
        $filePath = __DIR__ . '/fixtures/json-file-helper-test/tmp.json';
        $array = [
            'key' => 'val',
            'keys' => [0, 1, 2],
        ];
        JsonFileHelper::write($filePath, $array);

        $this->assertSame(
            '{
    "key": "val",
    "keys": [
        0,
        1,
        2
    ]
}',
            file_get_contents($filePath)
        );
        unlink($filePath);
    }

    public function testWriteToNonExistingDirectorySuccessfully(): void
    {
        $filePath = __DIR__ . '/non-existing-folder/tmp.json';
        $array = [
            'key' => 'val',
        ];

        JsonFileHelper::write($filePath, $array, false);
        $this->assertSame('{"key":"val"}', file_get_contents($filePath));

        unlink($filePath);
        rmdir(pathinfo($filePath, PATHINFO_DIRNAME));
    }

    public function testWriteToProtectedDirectoryThrowsException(): void
    {
        $filePath =  '/tmp.json';
        $array = ['key'];

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessageRegExp('~^file_put_contents(.*): failed to open stream: Permission denied$~');
        JsonFileHelper::write($filePath, $array);
    }
}
