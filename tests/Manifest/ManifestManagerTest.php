<?php declare(strict_types = 1);

namespace Keboola\Component\Tests\Manifest;

use Keboola\Component\Manifest\ManifestManager;
use PHPUnit\Framework\TestCase;

class ManifestManagerTest extends TestCase
{
    /**
     * @dataProvider provideFilenameForGetManifestFilename
     */
    public function testGetManifestFilename(string $expected, string $filename): void
    {
        $this->assertSame(
            $expected,
            ManifestManager::getManifestFilename($filename)
        );
    }

    /**
     * @return string[][]
     */
    public function provideFilenameForGetManifestFilename(): array
    {
        return [
            'file with extension' => [
                '/some/file.csv.manifest',
                '/some/file.csv',
            ],
            'file that is already manifest' => [
                '/some/file.csv.manifest',
                '/some/file.csv.manifest',
            ],
            'file without extension' => [
                '/some/file.manifest',
                '/some/file',
            ],
        ];
    }

    public function testWillWriteFileManifest(): void
    {
        $manager = new ManifestManager('/data/');

        $fileName = __DIR__ . '/fixtures/file.csv';
        $manager->writeFileManifest($fileName, ['sometag'], false, false, true, false);

        $manifestFilename = $fileName . '.manifest';
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/fixtures/expected-file.manifest', $manifestFilename);
        unlink($manifestFilename);
    }

    public function testWillWriteTableManifest(): void
    {
        $manager = new ManifestManager('/data/');

        $fileName = __DIR__ . '/fixtures/table.csv';
        $metadata = [
            [
                'key' => 'an.arbitrary.key',
                'value' => 'Some value',
            ],
            [
                'key' => 'another.arbitrary.key',
                'value' => 'A different value',
            ],
        ];
        $columnMetadata = [
            'column1' => [
                [
                    'key' => 'yet.another.key',
                    'value' => 'Some other value',
                ],
            ],
        ];
        $manager->writeTableManifest(
            $fileName,
            'destination-table',
            ['id', 'number'],
            ';',
            '\'',
            ['id', 'number', 'other_column'],
            false,
            $metadata,
            $columnMetadata
        );

        $manifestFilename = $fileName . '.manifest';
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/fixtures/expected-table.manifest', $manifestFilename);
        unlink($manifestFilename);
    }

    public function testWillWriteTableManifestWithoutExtension(): void
    {
        $manager = new ManifestManager('/data/');

        $fileName = __DIR__ . '/fixtures/table';
        $metadata = [
            [
                'key' => 'an.arbitrary.key',
                'value' => 'Some value',
            ],
            [
                'key' => 'another.arbitrary.key',
                'value' => 'A different value',
            ],
        ];
        $columnMetadata = [
            'column1' => [
                [
                    'key' => 'yet.another.key',
                    'value' => 'Some other value',
                ],
            ],
        ];
        $manager->writeTableManifest(
            $fileName,
            'destination-table',
            ['id', 'number'],
            ';',
            '\'',
            ['id', 'number', 'other_column'],
            false,
            $metadata,
            $columnMetadata
        );

        $manifestFilename = $fileName . '.manifest';
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/fixtures/expected-table.manifest', $manifestFilename);
        unlink($manifestFilename);
    }

    public function testWillLoadFileManifest(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = [
            'is_permanent' => false,
            'is_public' => false,
            'tags' => [
                'sometag',
            ],
            'notify' => true,
        ];
        $this->assertSame($expectedManifest, $manager->getFileManifest('people.csv'));
    }

    public function testWillLoadTableManifest(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = [
            'destination' => 'destination-table',
            'primary_key' => [
                'id',
                'number',
            ],
        ];
        $this->assertSame($expectedManifest, $manager->getTableManifest('people.csv'));
    }

    public function testWillLoadTableManifestWithoutCsv(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = [
            'destination' => 'destination-table',
            'primary_key' => [
                'id',
                'number',
            ],
        ];
        $this->assertSame($expectedManifest, $manager->getTableManifest('products'));
    }

    /**
     * @param string $tableName
     * @dataProvider provideTableNameForManifestReadWriteTest
     */
    public function testReadAndWrittenManifestAreTheSame(string $tableName): void
    {
        $dataDir = __DIR__ . '/fixtures/manifest-data-dir';
        $manifestDirectory = implode('/', [$dataDir, 'in', 'tables']);
        $generatedTableName = $tableName . '-generated';
        $generatedManifestFilename = $manifestDirectory . '/' . $generatedTableName;
        $manager = new ManifestManager($dataDir);
        $manifest = $manager->getTableManifest($tableName);

        $manager->writeTableManifestFromArray(
            $generatedManifestFilename,
            $manifest
        );
        $generatedManifest = $manager->getTableManifest($generatedTableName);

        // generated manifest will include all fields due to default values
        $this->assertGreaterThanOrEqual(
            count($manifest),
            count($generatedManifest)
        );
        foreach ($manifest as $key => $value) {
            // every key from original must be preserved
            $this->assertSame(
                $manifest[$key],
                $generatedManifest[$key],
                sprintf('Key "%s" should be the same', $key)
            );
        }

        // cleanup
        unlink(ManifestManager::getManifestFilename($generatedManifestFilename));
    }

    /**
     * @return string[][]
     */
    public function provideTableNameForManifestReadWriteTest(): array
    {
        return [
            [
                'delimiter-and-enclosure',
            ],
            [
                'full-featured',
            ],
        ];
    }
}
