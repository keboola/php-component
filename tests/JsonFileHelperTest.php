<?php

declare(strict_types=1);

namespace Keboola\Component\Tests;

use Keboola\Component\JsonFileHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class JsonFileHelperTest extends TestCase
{
    /** @var JsonFileHelper */
    private $jsonFileHelper;

    public function setUp(): void
    {
        $this->jsonFileHelper = new JsonFileHelper();
    }

    public function testReadNonExistingFileThrowsException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File "/dev/null/file.json" could not be found.');
        $this->jsonFileHelper->read('/dev/null/file.json');
    }

    public function testReadInvalidFileThrowsException(): void
    {
        $this->expectException(NotEncodableValueException::class);
        $this->expectExceptionMessage('Syntax error');
        $this->jsonFileHelper->read(__DIR__ . '/fixtures/json-file-helper-test/invalidJsonFile.json');
    }

    public function testReadFileSuccessfully(): void
    {
        $array = $this->jsonFileHelper->read(__DIR__ . '/fixtures/json-file-helper-test/file.json');
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
        $this->jsonFileHelper->write($filePath, $array);

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
        $this->jsonFileHelper->write($filePath, $array, ['json_encode_options' => JSON_PRETTY_PRINT]);

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

    public function testWriteToNonExistingDirectoryThrowsException(): void
    {
        $filePath = __DIR__ . '/non-existing-folder/tmp.json';
        $array = [
            'key' => 'val',
        ];

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage(
            'file_put_contents(/Users/ds/projects/keboola/php-component/tests/non-existing-folder/tmp.json):'
            . ' failed to open stream: No such file or directory'
        );
        $this->jsonFileHelper->write($filePath, $array);
    }
}
