<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Manifest\ManifestManager\Options;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
use PHPUnit\Framework\TestCase;

class OutTableManifestOptionsTest extends TestCase
{
    /**
     * @dataProvider provideOptions
     * @param mixed[] $expected
     * @param OutTableManifestOptions $options
     */
    public function testToArray(array $expected, OutTableManifestOptions $options): void
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
                (new OutTableManifestOptions())
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
                    'column_metadata' => [
                        'column1' => [
                            [
                                'key' => 'yet.another.key',
                                'value' => 'Some other value',
                            ],
                        ],
                    ],
                ],
                (new OutTableManifestOptions())
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
                            'value' => 'A different value',
                            'key' => 'another.arbitrary.key',
                        ],
                    ])
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
            [
                'Each metadata item must be an array',
                function (): void {
                    (new OutTableManifestOptions())->setMetadata([
                        'one',
                        'two',
                    ]);
                },
            ],
            [
                'Each metadata item must have only "key" and "value" keys',
                function (): void {
                    (new OutTableManifestOptions())->setMetadata([
                        [
                            'key' => 'my-key',
                            'value' => 'my-value',
                            'something' => 'my-value',
                        ],
                    ]);
                },
            ],
            [
                'Each metadata item must have only "key" and "value" keys',
                function (): void {
                    (new OutTableManifestOptions())->setMetadata([
                        [
                            'key' => 'my-key',
                            'something' => 'my-value',
                        ],
                    ]);
                },
            ],
        ];
    }
}
