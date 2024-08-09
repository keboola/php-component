<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Manifest\ManifestManager\Options;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;

class OutTableManifestOptionsTest extends TestCase
{
    /**
     * @dataProvider provideOptions
     * @param mixed[] $expected
     */
    public function testToArray(array $expected, ManifestOptions $options): void
    {
        $this->assertEquals($expected, $options->toArray(false));
    }

    /**
     * @dataProvider provideOptions
     * @param mixed[] $options
     */
    public function testFromArray(array $options, ManifestOptions $expected): void
    {
        $this->assertEquals($expected, ManifestOptions::fromArray($options));
    }

    /**
     * @return mixed[][]
     */
    public function provideOptions(): array
    {
        return [
            'some options' => [
                [
                    'delimiter' => '|',
                    'enclosure' => '_',
                    'manifest_type' => ManifestOptions::MANIFEST_TYPE_OUTPUT,

                ],
                (new ManifestOptions())
                    ->setDelimiter('|')
                    ->setEnclosure('_')
                    ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT),
            ],
            'all options' => [
                [
                    'destination' => 'my.table',
                    'manifest_type' => ManifestOptions::MANIFEST_TYPE_OUTPUT,
                    'delete_where_column' => 'column1',
                    'delete_where_values' => ['value1'],
                    'delete_where_operator' => 'eq',
                    'delimiter' => '|',
                    'enclosure' => '_',
                    'incremental' => true,
                    'schema' => [
                        [
                            'nullable' => true,
                            'primary_key' => true,
                            'name' => 'id',
                        ],
                        [
                            'nullable' => true,
                            'primary_key' => false,
                            'name' => 'number',
                        ],
                        [
                            'nullable' => true,
                            'primary_key' => false,
                            'name' => 'other_column',
                        ],
                    ],
                    'table_metadata' => [
                        'an.arbitrary.key' => 'Some value',
                        'another.arbitrary.key' => 'A different value',
                    ],
                ],
                (new ManifestOptions())
                    ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
                    ->setDeleteWhereColumn('column1')
                    ->setDeleteWhereValues(['value1'])
                    ->setDeleteWhereOperator('eq')
                    ->setEnclosure('_')
                    ->setDelimiter('|')
                    ->setColumns(['id', 'number', 'other_column'])
                    ->setColumnMetadata((object) [
                        '123456' => [
                            [
                                'value' => 'Int column name',
                                'key' => 'int.column.name',
                            ],
                        ],
                        'column1' => [
                            [
                                'value' => 'Some other value',
                                'key' => 'yet.another.key',
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
                            'value' => 'A different value',
                            'key' => 'another.arbitrary.key',
                        ],
                    ])
                    ->setPrimaryKeyColumns(['id']),
            ],
            'new native datatypes manifest' => [
                [
                    'destination' => 'my.table',
                    'delimiter' => '|',
                    'enclosure' => '_',
                    'manifest_type' => ManifestOptions::MANIFEST_TYPE_OUTPUT,
                    'schema' => [
                        [
                            'name' => 'id',
                            'data_type' => [
                                'base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123'],
                                'bigquery' => ['type' => 'VARCHAR', 'length' => '255'],
                            ],
                            'nullable' => false,
                            'primary_key' => true,
                            'metadata' => [
                                'KBC.description' => 'Primary key column',
                            ],
                        ],
                    ],
                    'incremental' => true,
                ],
                (new ManifestOptions())
                    ->setEnclosure('_')
                    ->setDelimiter('|')
                    ->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT)
                    ->addSchema(new ManifestOptionsSchema(
                        'id',
                        [
                            'base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123'],
                            'bigquery' => ['type' => 'VARCHAR', 'length' => '255'],
                        ],
                        false,
                        true,
                        null,
                        ['KBC.description' => 'Primary key column'],
                    ))
                    ->setDestination('my.table')
                    ->setIncremental(true),
            ],
        ];
    }

    /** @dataProvider validNamesProvider */
    public function testManifestOptionsSchemaValidNames(string $name): void
    {
        $manifestOptionsSchema = new ManifestOptionsSchema(
            $name,
            ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
            false,
            true,
        );

        $this->assertSame($name, $manifestOptionsSchema->getName());
    }

    public function validNamesProvider(): array
    {
        return [
            'simple name' => ['id'],
            'numeric' => ['123'],
            'zero' => ['0'],
            'string null' => ['null'],
            'space' => [' '],
        ];
    }

    public function testFromArrayWithNonExistingPrimaryKey(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Primary keys do not match columns. Missing columns: number');

        ManifestOptions::fromArray([
            'destination' => 'my.table',
            'columns' => ['id', 'number #', 'other_column'],
            'incremental' => true,
            'primary_key' => ['id', 'number'],
        ]);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Primary keys do not match columns. ' .
            'Missing columns: non-existing-column-1, non-existing-column-2');

        ManifestOptions::fromArray([
            'destination' => 'my.table',
            'columns' => ['id', 'number', 'other_column'],
            'incremental' => true,
            'primary_key' => ['id', 'non-existing-column-1', 'non-existing-column-2'],
        ]);
    }

    /**
     * @dataProvider provideInvalidOptions
     */
    public function testInvalidOptions(string $expectedExceptionMessage, callable $callWithInvalidArguments): void
    {
        $this->expectException(OptionsValidationException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $callWithInvalidArguments();
    }

    /**
     * @return mixed[][]
     */
    public function provideInvalidOptions(): array
    {
        return [
            'non-array metadata' => [
                'Metadata item #0 must be an array, found "string"',
                function (): void {
                    (new ManifestOptions())->setMetadata([
                        'one',
                        'two',
                    ]);
                },
            ],
            'metadata with extra key' => [
                'Metadata item #0 must have only "key" and "value" keys',
                function (): void {
                    (new ManifestOptions())->setMetadata([
                        [
                            'key' => 'my-key',
                            'value' => 'my-value',
                            'something' => 'my-value',
                        ],
                    ]);
                },
            ],
            'missing one of the metadata keys' => [
                'Metadata item #0 must have only "key" and "value" keys',
                function (): void {
                    (new ManifestOptions())->setMetadata([
                        [
                            'key' => 'my-key',
                            'something' => 'my-value',
                        ],
                    ]);
                },
            ],
            'Column metadata is not array' => [
                'Each column metadata item must be an array',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        'x',
                    ]);
                },
            ],
            'Column name is not a string' => [
                'Each column metadata item must have string key',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        ['x'],
                    ]);
                },
            ],
            'Column metadata item is not an array' => [
                'Column "column1": Metadata item #0 must be an array, found "string"',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        'column1' => ['x'],
                    ]);
                },
            ],
            'Column metadata item is missing required keys' => [
                'Column "column1": Metadata item #0 must have only "key" and "value" keys',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        'column1' => [
                            ['some' => 'x'],
                        ],
                    ]);
                },
            ],
            'Column metadata item has extra keys' => [
                'Column "column1": Metadata item #1 must have only "key" and "value" keys',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        'column1' => [
                            [
                                'key' => 'x',
                                'value' => 'y',
                            ],
                            [
                                'key' => 'x',
                                'value' => 'y',
                                'string' => 'is not',
                            ],
                        ],
                    ]);
                },
            ],
            'Cannot set column metadata before columns/schema' => [
                'Set schema (or columns) first.',
                function (): void {
                    (new ManifestOptions())
                        ->setColumnMetadata([
                            'id' => [
                                ['key' => 'description', 'value' => 'ID column'],
                            ],
                        ])
                        ->setColumns(['id']);
                },
            ],
            'Cannot set primary keys before columns/schema' => [
                'Set schema (or columns) first.',
                function (): void {
                    (new ManifestOptions())
                        ->setPrimaryKeyColumns(['id'])
                        ->setColumns(['id']);
                },
            ],
            'The "unsupported_type" backendType is not supported' => [
                'The "unsupported_type" backendType is not supported',
                function (): void {
                    new ManifestOptionsSchema(
                        'id',
                        ['unsupported_type' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                    );
                },
            ],
            'Name cannot be empty' => [
                'Name cannot be empty',
                function (): void {
                    new ManifestOptionsSchema(
                        '',
                        ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                    );
                },
            ],
            'Only one of "description" or "metadata.KBC.description" can be defined' => [
                'Only one of "description" or "metadata.KBC.description" can be defined',
                function (): void {
                    new ManifestOptionsSchema(
                        'id',
                        ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                        'Primary key column',
                        ['KBC.description' => 'Primary key column'],
                    );
                },
            ],
        ];
    }
}
