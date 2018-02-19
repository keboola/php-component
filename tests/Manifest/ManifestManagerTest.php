<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Tests\Manifest;

use Keboola\DockerApplication\Manifest\ManifestManager;
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
        $manager->writeFileManifest($fileName, ['sometag'], false, false, true);

        $manifestFilename = $fileName . '.manifest';
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/fixtures/expected-file.manifest', $manifestFilename);
        unlink($manifestFilename);
    }

    public function testWillWriteTableManifest(): void
    {
        $manager = new ManifestManager('/data/');

        $fileName = __DIR__ . '/fixtures/table.csv';
        $manager->writeTableManifest($fileName, 'destination-table', ['id', 'number']);

        $manifestFilename = $fileName . '.manifest';
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/fixtures/expected-table.manifest', $manifestFilename);
        unlink($manifestFilename);
    }

    public function testWillWriteTableManifestWithoutExtension(): void
    {
        $manager = new ManifestManager('/data/');

        $fileName = __DIR__ . '/fixtures/table';
        $manager->writeTableManifest($fileName, 'destination-table', ['id', 'number']);

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
}
