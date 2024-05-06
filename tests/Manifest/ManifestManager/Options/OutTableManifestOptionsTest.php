<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Manifest\ManifestManager\Options;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use PHPUnit\Framework\TestCase;

class OutTableManifestOptionsTest extends TestCase
{
    /**
     * @dataProvider provideOptions
     * @param mixed[] $expected
     */
    public function testToArray(array $expected, ManifestOptions $options): void
    {
        $this->assertEquals($expected, $options->toArray());
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

                ],
                (new ManifestOptions())
                    ->setDelimiter('|')
                    ->setEnclosure('_'),
            ],
            'all options' => [
                [
                    'destination' => 'my.table',
                    'primary_key' => ['id'],
                    'delimiter' => '|',
                    'enclosure' => '_',
                    'columns' => [
                        'id',
                        'number',
                        'other_column',
                    ],
                    'incremental' => true,
                    'metadata' => [
                        [
                            'key' => 'an.arbitrary.key',
                            'value' => 'Some value',
                        ],
                        [
                            'key' => 'another.arbitrary.key',
                            'value' => 'A different value',
                        ],
                    ],
                    'column_metadata' => (object) [
                        '123456' => [
                            [
                                'key' => 'int.column.name',
                                'value' => 'Int column name',
                            ],
                        ],
                        'column1' => [
                            [
                                'key' => 'yet.another.key',
                                'value' => 'Some other value',
                            ],
                        ],
                    ],
                ],
                (new ManifestOptions())
                    ->setEnclosure('_')
                    ->setDelimiter('|')
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
                    ->setColumns(['id', 'number', 'other_column'])
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
                    'primary_key' => ['id'],
                    'delimiter' => '|',
                    'enclosure' => '_',
                    'manifest_type' => 'output',
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
                    ->setManifestType('output')
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
                    ->setIncremental(true)
                    ->setPrimaryKeyColumns(['id']),
            ],
        ];
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
            'Metadata item #0 must be an array, found "string"' => [
                'Metadata item #0 must be an array, found "string"',
                function (): void {
                    (new ManifestOptions())->setMetadata([
                        'one',
                        'two',
                    ]);
                },
            ],
            'Metadata item #0 must have only "key" and "value" keys' => [
                'Metadata item #0 must have only "key" and "value" keys',
                function (): void {
                    (new ManifestOptions())->setMetadata([
                        [
                            'key' => 'my-key',
                            'value' => 'my-value',
                            'extra' => 'should not be here',
                        ],
                    ]);
                },
            ],
            'Each column metadata item must be an array' => [
                'Each column metadata item must be an array',
                function (): void {
                    (new ManifestOptions())->setColumnMetadata([
                        'x',
                    ]);
                },
            ],
            'Cannot set columns when schema is set' => [
                'Cannot set columns when schema is set',
                function (): void {
                    $schema = new ManifestOptionsSchema(
                        'id',
                        ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                    );
                    (new ManifestOptions())
                        ->addSchema($schema)
                        ->setColumns(['id', 'name']);
                },
            ],
            'Cannot set schema when columns are set' => [
                'Cannot set schema when columns are set',
                function (): void {
                    (new ManifestOptions())
                        ->setColumns(['id', 'name'])
                        ->addSchema(new ManifestOptionsSchema(
                            'id',
                            ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                            false,
                            true,
                        ));
                },
            ],
            'Cannot set metadata when schema is set' => [
                'Cannot set metadata when schema is set',
                function (): void {
                    $schema = new ManifestOptionsSchema(
                        'id',
                        ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                    );
                    (new ManifestOptions())
                        ->addSchema($schema)
                        ->setMetadata([
                            ['key' => 'sample', 'value' => 'data'],
                        ]);
                },
            ],
            'Cannot set column metadata when schema is set' => [
                'Cannot set column metadata when schema is set',
                function (): void {
                    $schema = new ManifestOptionsSchema(
                        'id',
                        ['base' => ['type' => 'INTEGER', 'length' => '11', 'default' => '123']],
                        false,
                        true,
                    );
                    (new ManifestOptions())
                        ->addSchema($schema)
                        ->setColumnMetadata([
                            'id' => [
                                ['key' => 'description', 'value' => 'ID column'],
                            ],
                        ]);
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
