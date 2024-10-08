<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Manifest;

use Generator;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutFileManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchemaDataType;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;

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
            $manifestManager->getManifestFilename($filename),
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

        $manager->writeFileManifest(
            $fileName,
            (new OutFileManifestOptions())
                ->setTags(['sometag'])
                ->setIsPublic(false)
                ->setIsPermanent(false)
                ->setNotify(true)
                ->setIsEncrypted(false),
        );

        $this->assertJsonFileEqualsJsonFile(
            __DIR__ . '/fixtures/expected-file.manifest',
            $dataDir . '/out/files/file.jpg.manifest',
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
            'columns' => [
                'id',
                'number',
                'name',
                'description',
                'created_at',
                'updated_at',
            ],
            'primary_key' => [
                'id',
                'number',
            ],
            'column_metadata' => [
                'id' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'number' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'name' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'description' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'created_at' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'updated_at' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedManifest, $manager->getTableManifest('people.csv')->toArray());
    }

    public function testManifestWithOnlyPrimaryKeysSpecified(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = [
            'destination' => 'destination-table',
            'primary_key' => [
                'id',
                'number',
            ],
        ];

        $this->assertSame($expectedManifest, $manager->getTableManifest('onlyPrimaryKeys.csv')->toArray());

        // Test that toArray(legacy: false) force fallbacks to the same result
        $this->assertSame(
            $expectedManifest,
            $manager->getTableManifest('onlyPrimaryKeys.csv')->toArray(false),
        );
    }

    /**
     * @dataProvider legacyPrimaryKeysDataProvider
     */
    public function testManifestObjectWithExtraPrimaryKeysSpecified(
        array $legacyPrimaryKeys,
        array $schema,
        array $expectedManifest,
    ): void {
        $manifest = (new ManifestOptions())
            ->setDestination('destination-table')
            ->setLegacyPrimaryKeys($legacyPrimaryKeys)
            ->setSchema($schema);

        $this->assertEqualsCanonicalizing($expectedManifest, $manifest->toArray());

        // Test that toArray(legacy: false) force fallbacks to the same result
        $this->assertEqualsCanonicalizing(
            $expectedManifest,
            $manifest->toArray(false),
        );
    }

    public function legacyPrimaryKeysDataProvider(): Generator
    {
        yield 'two columns, three legacy primary keys merging with two non-legacy' => [
            ['id', 'number', 'name'],
            [
                new ManifestOptionsSchema(
                    'id',
                    [],
                    false,
                    true,
                ),
                new ManifestOptionsSchema(
                    'number',
                    ['base' => ['type' => 'INTEGER', 'default' => '0']],
                    false,
                    true,
                ),
            ],
            [
                'destination' => 'destination-table',
                'columns' => ['id', 'number'],
                'primary_key' => ['id', 'number', 'name'],
                'column_metadata' => [
                    'id' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                    ],
                    'number' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                        [
                            'key' => 'KBC.datatype.basetype',
                            'value' => 'INTEGER',
                        ],
                        [
                            'key' => 'KBC.datatype.default',
                            'value' => '0',
                        ],
                    ],
                ],
            ],
        ];

        yield 'two columns, one legacy primary key, two non-legacy' => [
            ['name'],
            [
                new ManifestOptionsSchema(
                    'id',
                    [],
                    false,
                    true,
                ),
                new ManifestOptionsSchema(
                    'number',
                    ['base' => ['type' => 'INTEGER', 'default' => '0']],
                    false,
                    true,
                ),
            ],
            [
                'destination' => 'destination-table',
                'columns' => ['id', 'number'],
                'primary_key' => ['id', 'number', 'name'],
                'column_metadata' => [
                    'id' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                    ],
                    'number' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                        [
                            'key' => 'KBC.datatype.basetype',
                            'value' => 'INTEGER',
                        ],
                        [
                            'key' => 'KBC.datatype.default',
                            'value' => '0',
                        ],
                    ],
                ],
            ],
        ];

        yield 'two columns, three legacy primary keys' => [
            ['id', 'number', 'name'],
            [
                new ManifestOptionsSchema(
                    'id',
                    [],
                    false,
                    false,
                ),
                new ManifestOptionsSchema(
                    'number',
                    ['base' => ['type' => 'INTEGER', 'default' => '0']],
                    false,
                    false,
                ),
            ],
            [
                'destination' => 'destination-table',
                'columns' => ['id', 'number'],
                'primary_key' => ['id', 'number', 'name'],
                'column_metadata' => [
                    'id' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                    ],
                    'number' => [
                        [
                            'key' => 'KBC.datatype.nullable',
                            'value' => false,
                        ],
                        [
                            'key' => 'KBC.datatype.basetype',
                            'value' => 'INTEGER',
                        ],
                        [
                            'key' => 'KBC.datatype.default',
                            'value' => '0',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testNonexistentManifestReturnsEmptyArray(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $this->assertSame([], $manager->getTableManifest('manifest-does-not-exist')->toArray());
    }

    public function testWillLoadTableManifestWithoutCsv(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = [
            'destination' => 'destination-table',
            'columns' => [
                'id',
                'number',
                'name',
            ],
            'primary_key' => [
                'id',
                'number',
            ],
            'column_metadata' => [
                'id' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'number' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
                'name' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedManifest, $manager->getTableManifest('products')->toArray());
    }

    public function testLoadLegacyTableManifestAsObject(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = (new ManifestOptions())
            ->setEnclosure('_')
            ->setDelimiter('|')
            ->setDestination('my.table')
            ->setIncremental(true)
            ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
            ->setTableMetadata([
                'an.arbitrary.key' => 'Some value',
                'another.arbitrary.key' => 'A different value',
            ])
            ->setSchema([
                new ManifestOptionsSchema(
                    'id',
                    [],
                    true,
                    true,
                    null,
                    [
                        'yet.another.key' => 'Some other value',
                    ],
                ),
                new ManifestOptionsSchema(
                    'number',
                    ['base' => ['type' => 'INTEGER', 'default' =>'0']],
                ),
                new ManifestOptionsSchema(
                    'other_column',
                    ['base' => ['type' => 'BOOLEAN', 'default' => 'false']],
                ),
            ]);

        $this->assertEquals(
            $expectedManifest,
            $manager->getTableManifest('legacy'),
        );
    }

    public function testLoadNewDataTypesTableManifestAsObject(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = (new ManifestOptions())
            ->setDestination('my-table')
            ->setIncremental(true)
            ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
            ->setHasHeader(true)
            ->setDescription('Best table')
            ->setSchema([
                new ManifestOptionsSchema(
                    'id',
                    [
                        'base' => [
                            'type' => 'INTEGER',
                            'length' => '11',
                            'default' => '123',
                        ],
                    ],
                    false,
                    true,
                    'This is a primary key',
                    ['yet.another.key' => 'Some other value'],
                ),
                new ManifestOptionsSchema(
                    'number',
                    [
                        'base' => [
                            'type' => 'VARCHAR',
                            'length' => '255',
                        ],
                    ],
                    true,
                ),
                new ManifestOptionsSchema(
                    'other_column',
                    [
                        'base' => [
                            'type' => 'VARCHAR',
                            'length' => '255',
                        ],
                    ],
                    true,
                ),
            ])
            ->setTableMetadata([
                'an.arbitrary.key' => 'Some value',
                'another.arbitrary.key' => 'Another value',
            ]);

        $this->assertEquals(
            $expectedManifest,
            $manager->getTableManifest('newDatatypes'),
        );
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
     */
    public function testWriteTableManifest(string $expected, ManifestOptions $options): void
    {
        $temp = new Temp('testWriteManifestFromOptions');
        $dataDir = $temp->getTmpFolder();
        $manifestManager = new ManifestManager($dataDir);

        $manifestManager->writeTableManifest(
            'my-table',
            $options,
        );

        $this->assertJsonFileEqualsJsonFile(
            $expected,
            $dataDir . '/out/tables/my-table.manifest',
        );
    }

    /**
     * @return array<string, array{string, ManifestOptions}>
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function provideWriteManifestOptions(): array
    {
        return [
            'writes only some' => [
                __DIR__ . '/fixtures/expectedManifestForWriteFromOptionsSomeOptions.manifest',
                (new ManifestOptions())
                    ->setDelimiter('|')
                    ->setEnclosure('_'),
            ],
            'write all options' => [
                __DIR__ . '/fixtures/expectedManifestForWriteFromOptionsAllOptions.manifest',
                (new ManifestOptions())
                    ->setEnclosure('_')
                    ->setDelimiter('|')
                    ->setColumns(['id', 'number', 'other_column'])
                    ->setColumnMetadata([
                        'id' => [
                            [
                                'key' => 'yet.another.key',
                                'value' => 'Some other value',
                            ],
                            [
                                'key' => 'yet.another.key', //duplicated key should be removed
                                'value' => 'Some other value',
                            ],
                        ],
                    ])
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
            'write new datatype manifest' => [
                __DIR__ . '/fixtures/expectedNewDataTypesManifest.manifest',
                (new ManifestOptions())
                    ->setDestination('my-table')
                    ->setIncremental(true)
                    ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
                    ->setHasHeader(true)
                    ->setDescription('Best table')
                    ->setSchema([
                        new ManifestOptionsSchema(
                            'id',
                            [
                                'base' => [
                                    'type' => 'INTEGER',
                                    'length' => '11',
                                    'default' => '123',
                                ],
                            ],
                            false,
                            true,
                            'This is a primary key',
                            ['yet.another.key' => 'Some other value'],
                        ),
                        new ManifestOptionsSchema(
                            'number',
                            [
                                'base' => [
                                    'type' => 'VARCHAR',
                                    'length' => '255',
                                ],
                            ],
                            true,
                        ),
                        new ManifestOptionsSchema(
                            'other_column',
                            [
                                'base' => [
                                    'type' => 'VARCHAR',
                                    'length' => '255',
                                ],
                            ],
                            true,
                        ),
                    ])
                    ->setTableMetadata([
                        'an.arbitrary.key' => 'Some value',
                        'another.arbitrary.key' => 'Another value',
                    ]),
            ],
        ];
    }

    public function testConvertLegacyManifestToNewManifestObject(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifest = (new ManifestOptions())
            ->setDestination('my.table')
            ->setDelimiter('|')
            ->setEnclosure('_')
            ->setIncremental(true)
            ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
            ->setSchema([
                new ManifestOptionsSchema(
                    'id',
                    null,
                    true,
                    true,
                    null,
                    ['yet.another.key' => 'Some other value'],
                ),
                new ManifestOptionsSchema(
                    'number',
                    ['base' => ['type' => 'INTEGER', 'default' =>'0']],
                ),
                new ManifestOptionsSchema(
                    'other_column',
                    ['base' => ['type' => 'BOOLEAN', 'default' => 'false']],
                ),
            ])
            ->setTableMetadata([
                'an.arbitrary.key' => 'Some value',
                'another.arbitrary.key' => 'A different value',
            ]);

        $this->assertEquals(
            $expectedManifest,
            $manager->getTableManifest('legacy'),
        );
    }

    public function testConvertNewDataTypesManifestToLegacyArray(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $expectedManifestArray = [
            'destination' => 'my-table',
            'incremental' => true,
            'primary_key' => ['id'],
            'metadata' => [
                [
                    'key' => 'an.arbitrary.key',
                    'value' => 'Some value',
                ],
                [
                    'key' => 'another.arbitrary.key',
                    'value' => 'Another value',
                ],
            ],
            'columns' => ['id', 'number', 'other_column'],
            'column_metadata' => [
                'id' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => false,
                    ],
                    [
                        'key' => 'KBC.description',
                        'value' => 'This is a primary key',
                    ],
                    [
                        'key' => 'KBC.datatype.basetype',
                        'value' => 'INTEGER',
                    ],
                    [
                        'key' => 'KBC.datatype.length',
                        'value' => '11',
                    ],
                    [
                        'key' => 'KBC.datatype.default',
                        'value' => '123',
                    ],
                    [
                        'key' => 'yet.another.key',
                        'value' => 'Some other value',
                    ],
                ],
                'number' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                    [
                        'key' => 'KBC.datatype.basetype',
                        'value' => 'VARCHAR',
                    ],
                    [
                        'key' => 'KBC.datatype.length',
                        'value' => '255',
                    ],
                ],
                'other_column' => [
                    [
                        'key' => 'KBC.datatype.nullable',
                        'value' => true,
                    ],
                    [
                        'key' => 'KBC.datatype.basetype',
                        'value' => 'VARCHAR',
                    ],
                    [
                        'key' => 'KBC.datatype.length',
                        'value' => '255',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedManifestArray,
            $manager->getTableManifest('newDatatypes')->toArray(),
        );
    }

    public function testCompareSnflkExampleNewDataTypesManifestWithLegacyOneAsObjects(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $this->assertEquals(
            $manager->getTableManifest('snfklExampleNewDatatypes'),
            $manager->getTableManifest('snflkExampleLegacy'),
        );
    }

    public function testCompareSnflkExampleNewDataTypesManifestWithLegacyOneAsArraysInLegacyFromats(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $this->assertEqualsCanonicalizing(
            $manager->getTableManifest('snfklExampleNewDatatypes')->toArray(),
            $manager->getTableManifest('snflkExampleLegacy')->toArray(),
        );
    }

    public function testCompareSnflkExampleNewDataTypesManifestWithLegacyOneAsArraysInNewFormats(): void
    {
        $manager = new ManifestManager(__DIR__ . '/fixtures/manifest-data-dir');

        $this->assertEquals(
            $manager->getTableManifest('snfklExampleNewDatatypes')->toArray(false),
            $manager->getTableManifest('snflkExampleLegacy')->toArray(false),
        );
    }
}
