<?php declare(strict_types = 1);

namespace Keboola\Component\Tests\Manifest\ManifestManager\Options;

use Keboola\Component\Manifest\ManifestManager\Options\WriteTableManifestOptions;
use PHPUnit\Framework\TestCase;

class WriteTableManifestOptionsTest extends TestCase
{
    /**
     * @dataProvider provideOptions
     * @param mixed[] $expected
     * @param WriteTableManifestOptions $options
     */
    public function testToArray(array $expected, WriteTableManifestOptions $options): void
    {
        $this->assertSame($expected, $options->toArray());
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
                (new WriteTableManifestOptions())
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
