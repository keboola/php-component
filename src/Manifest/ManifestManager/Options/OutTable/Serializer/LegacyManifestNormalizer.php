<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LegacyManifestNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = [];

        /** @var ManifestOptions $object */
        $this->normalizeBasicProperties($object, $data);
        $this->normalizeTableMetadata($object, $data);
        $this->normalizeSchema($object, $data);

        return $data;
    }

    private function normalizeBasicProperties(ManifestOptions $object, array &$data): void
    {
        if ($object->getDestination() !== null) {
            $data['destination'] = $object->getDestination();
        }

        if ($object->isIncremental() !== null) {
            $data['incremental'] = $object->isIncremental();
        }

        if ($object->getDelimiter() !== null) {
            $data['delimiter'] = $object->getDelimiter();
        }

        if ($object->getEnclosure() !== null) {
            $data['enclosure'] = $object->getEnclosure();
        }
    }

    private function normalizeTableMetadata(ManifestOptions $object, array &$data): void
    {
        if ($object->getTableMetadata() !== null) {
            $data['metadata'] = [];
            foreach ($object->getTableMetadata() as $key => $value) {
                $data['metadata'][] = ['key' => $key, 'value' => $value];
            }
        }
    }

    private function normalizeSchema(ManifestOptions $object, array &$data): void
    {
        if ($object->getSchema() !== null) {
            $data['columns'] = [];
            foreach ($object->getSchema() as $schema) {
                $data['columns'][] = $schema->getName();
                $columnMetadata = [];

                if ($schema->isPrimaryKey()) {
                    $data['primary_key'][] = $schema->getName();
                }

                $this->normalizeDataTypes($schema, $columnMetadata);
                $this->normalizeColumnMetadata($schema, $columnMetadata);

                if (!empty($columnMetadata)) {
                    $data['column_metadata'][$schema->getName()] = $columnMetadata;
                }
            }
        }
    }

    private function normalizeDataTypes(ManifestOptionsSchema $schema, array &$columnMetadata): void
    {
        if ($schema->getDataType() !== null) {
            foreach ($schema->getDataType() as $backend => $typeInfo) {
                foreach ($typeInfo as $key => $value) {
                    $metaKey = ($backend === 'base' ? 'KBC.datatype.basetype' : 'KBC.datatype.' . $key);
                    $columnMetadata[] = ['key' => $metaKey, 'value' => $value];
                }
            }
        }
    }

    private function normalizeColumnMetadata(ManifestOptionsSchema $schema, array &$columnMetadata): void
    {
        if ($schema->getMetadata() !== null) {
            foreach ($schema->getMetadata() as $key => $value) {
                $columnMetadata[] = ['key' => $key, 'value' => $value];
            }
        }
    }

    /**
     * @param array $data
     * @throws OptionsValidationException
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): object
    {
        $manifestOptions = new ManifestOptions();
        $manifestOptions->setManifestType(ManifestOptions::MANIFEST_TYPE_OUTPUT);

        $this->setBasicProperties($manifestOptions, $data);
        $metadataBackend = $this->setTableMetadata($manifestOptions, $data);

        if (isset($data['primary_key']) && !isset($data['columns'])) {
            throw new OptionsValidationException('Columns must be specified when primary key is specified.');
        }

        if (isset($data['columns'])) {
            $this->setSchema($manifestOptions, $data, $metadataBackend);
        }

        return $manifestOptions;
    }

    private function setBasicProperties(ManifestOptions $manifestOptions, array $data): void
    {
        if (isset($data['destination'])) {
            $manifestOptions->setDestination((string) $data['destination']);
        }

        if (isset($data['incremental'])) {
            $manifestOptions->setIncremental((bool) $data['incremental']);
        }

        if (isset($data['delimiter'])) {
            $manifestOptions->setDelimiter((string) $data['delimiter']);
        }

        if (isset($data['enclosure'])) {
            $manifestOptions->setEnclosure((string) $data['enclosure']);
        }
    }

    private function setTableMetadata(ManifestOptions $manifestOptions, array $data): ?string
    {
        $tableMetadata = [];
        $metadataBackend = null;

        if (isset($data['metadata'])) {
            foreach ($data['metadata'] as $metadata) {
                if ($metadata['key'] === 'KBC.datatype.backend') {
                    $metadataBackend = $metadata['value'];
                } else {
                    $tableMetadata[$metadata['key']] = $metadata['value'];
                }
            }
            $manifestOptions->setTableMetadata($tableMetadata);
        }

        return $metadataBackend;
    }

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    private function setSchema(
        ManifestOptions $manifestOptions,
        array $data,
        ?string $metadataBackend
    ): void {
        $schema = [];

        foreach ($data['columns'] as $columnName) {
            $columnMetadata = $data['column_metadata'][$columnName] ?? [];
            $dataTypes = [];
            $metadata = [];
            $isNullable = true;
            $primaryKey = false;
            $description = null;

            foreach ($columnMetadata as $meta) {
                if (strpos($meta['key'], 'KBC.datatype.') === 0 && $meta['key'] !== 'KBC.datatype.nullable') {
                    $this->setDataType($meta, $dataTypes, $metadataBackend);
                } else {
                    $this->setMetadata($meta, $metadata, $description, $primaryKey, $isNullable);
                }
            }

            $isPK = $primaryKey ?: (isset($data['primary_key']) && in_array($columnName, $data['primary_key']));
            $schema[] = new ManifestOptionsSchema(
                $columnName,
                $dataTypes,
                $isNullable,
                $isPK,
                $description,
                empty($metadata) ? null : $metadata,
            );
        }

        $manifestOptions->setSchema($schema);
    }

    private function setDataType(array $meta, array &$dataTypes, ?string $defaultBackend): void
    {
        $key = (string) str_replace('KBC.datatype.', '', $meta['key']);
        if ($key === 'basetype') {
            $backend = 'base';
            $key = 'type';
        } else {
            $backend = $defaultBackend ?? 'base';
        }
        if (in_array($key, ['type', 'length', 'default'])) {
            $dataTypes['base'][$key] = $meta['value'];
            $dataTypes[$backend][$key] = $meta['value'];
        }
    }

    private function setMetadata(
        array $meta,
        array &$metadata,
        ?string &$description,
        bool &$primaryKey,
        bool &$isNullable
    ): void {
        if ($meta['key'] === 'KBC.description') {
            $description = $meta['value'];
        } elseif ($meta['key'] === 'KBC.primaryKey') {
            $primaryKey = (bool) $meta['value'];
        } elseif ($meta['key'] === 'KBC.datatype.nullable') {
            $isNullable = (bool) $meta['value'];
        } else {
            $metadata[$meta['key']] = $meta['value'];
        }
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ManifestOptions;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $type === ManifestOptions::class;
    }
}
