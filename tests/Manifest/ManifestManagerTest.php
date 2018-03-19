<?php declare(strict_types = 1);

namespace Keboola\Component\Tests\Manifest;

use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\WriteTableManifestOptions;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use function file_get_contents;

class ManifestManagerTest extends TestCase
{
    /**
     * @dataProvider provideFilenameForGetManifestFilename
     */
    public function testGetManifestFilename(string $expected, string $filename): void
    {
        $manifestManager = new ManifestManager('/data');
        $this->assertSame(
            $expected,
            $manifestManager->getManifestFilename($filename)
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
        $temp = new Temp('testWillWriteFileManifest');
        $dataDir = $temp->getTmpFolder();
        $manager = new ManifestManager($dataDir);
        $fileName = 'file.jpg';

        $manager->writeFileManifest($fileName, ['sometag'], false, false, true, false);

        $this->assertJsonFileEqualsJsonFile(
            __DIR__ . '/fixtures/expected-file.manifest',
            $dataDir . '/out/files/file.jpg.manifest'
        );
    }

    public function testWillWriteTableManifest(): void
    {
        $temp = new Temp('testWillWriteTableManifest');

        $dataDir = $temp->getTmpFolder();
        $manager = new ManifestManager($dataDir);

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
            'table.csv',
            'destination-table',
            ['id', 'number'],
            ['id', 'number', 'other_column'],
            false,
            $metadata,
            $columnMetadata,
            ';',
            '\''
        );

        $this->assertJsonFileEqualsJsonFile(
            __DIR__ . '/fixtures/expected-table.manifest',
            $dataDir . '/out/tables/table.csv.manifest'
        );
    }

    public function testWillWriteTableManifestWithoutExtension(): void
    {
        $temp = new Temp('testWillWriteTableManifest');
        $dataDir = $temp->getTmpFolder();
        $manager = new ManifestManager($dataDir);

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
            'table-name',
            'destination-table',
            ['id', 'number'],
            ['id', 'number', 'other_column'],
            false,
            $metadata,
            $columnMetadata,
            ';',
            '\''
        );

        $this->assertJsonFileEqualsJsonFile(
            __DIR__ . '/fixtures/expected-table.manifest',
            $dataDir . '/out/tables/table-name.manifest'
        );
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
     * @param string $inTableName
     * @dataProvider provideTableNameForManifestReadWriteTest
     */
    public function testReadAndWrittenManifestAreTheSame(string $inTableName): void
    {
        $dataDir = __DIR__ . '/fixtures/manifest-data-dir';
        $outTableName = $inTableName . '-generated';
        $manager = new ManifestManager($dataDir);

        $manifest = $manager->getTableManifest($inTableName);
        $manager->writeTableManifestFromArray(
            $outTableName,
            $manifest
        );

        // this needs to be done by hand as there is no API to reading manifest of out tables
        $encoder = new JsonEncoder();
        $generatedManifestFilePath = $dataDir . '/out/tables/' . $manager->getManifestFilename($outTableName);
        $generatedManifestContents = file_get_contents($generatedManifestFilePath);
        $generatedManifest = $encoder->decode($generatedManifestContents, JsonEncoder::FORMAT);
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
        unlink($generatedManifestFilePath);
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

    /**
     * @dataProvider provideWriteManifestOptions
     * @param string $expected
     * @param WriteTableManifestOptions $options
     */
    public function testWriteManifestFromOptions(string $expected, WriteTableManifestOptions $options): void
    {
        $temp = new Temp('testWriteManifestFromOptions');
        $dataDir = $temp->getTmpFolder();
        $manifestManager = new ManifestManager($dataDir);

        $manifestManager->writeTableManifestFromOptions(
            'my-table',
            $options
        );

        $this->assertJsonFileEqualsJsonFile(
            $expected,
            $dataDir . '/out/tables/my-table.manifest'
        );
    }

    /**
     * @return mixed[][]
     */
    public function provideWriteManifestOptions(): array
    {
        return [
            'writes only some' => [
                __DIR__ . '/fixtures/expectedManifestForWriteFromOptionsSomeOptions.manifest',
                (new WriteTableManifestOptions())
                    ->setDelimiter('|')
                    ->setEnclosure('_'),
            ],
            'write all options' => [
                __DIR__ . '/fixtures/expectedManifestForWriteFromOptionsAllOptions.manifest',
                (new WriteTableManifestOptions())
                    ->setEnclosure('_')
                    ->setDelimiter('|')
                    ->setColumnMetadata([
                        'column1' => [
                            [
                                'key' => 'yet.another.key',
                                'value' => 'Some other value',
                            ],
                        ],
                    ])
                    ->setColumns(['id', 'number', 'other_column'])
                    ->setDestination('my.table')
                    ->setIncremental(true)
                    ->setMetadata([
                        [
                            'key' => 'an.arbitrary.key',
                            'value' => 'Some value',
                        ],
                        [
                            'key' => 'another.arbitrary.key',
                            'value' => 'A different value',
                        ],
                    ])
                    ->setPrimaryKeyColumns(['id']),
            ],
        ];
    }
}
