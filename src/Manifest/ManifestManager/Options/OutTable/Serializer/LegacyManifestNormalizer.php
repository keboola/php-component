<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use Keboola\Component\UserException;
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
        if ($object->getLegacyPrimaryKeys() !== null) {
            if (!isset($data['primary_key'])) {
                $data['primary_key'] = [];
            }
            $data['primary_key'] = array_unique(array_merge($data['primary_key'], $object->getLegacyPrimaryKeys()));
        }

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

                $columnMetadata[] = ['key' => 'KBC.datatype.nullable', 'value' => $schema->isNullable()];
                if ($schema->getDescription() !== null) {
                    $columnMetadata[] = ['key' => 'KBC.description', 'value' => $schema->getDescription()];
                }

                $this->normalizeDataTypes($schema, $columnMetadata);
                $this->normalizeColumnMetadata($schema, $columnMetadata);
                $this->deduplicateMetadata($columnMetadata);

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
                $typeMetaKey = ($backend === 'base' ? 'KBC.datatype.basetype' : 'KBC.datatype.type');
                $columnMetadata[] = ['key' => $typeMetaKey, 'value' => $typeInfo->getType()];
                if ($typeInfo->getLength() !== null) {
                    $columnMetadata[] = ['key' => 'KBC.datatype.length', 'value' => $typeInfo->getLength()];
                }
                if ($typeInfo->getDefault() !== null) {
                    $columnMetadata[] = ['key' => 'KBC.datatype.default', 'value' => $typeInfo->getDefault()];
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
            $manifestOptions->setLegacyPrimaryKeys($data['primary_key']);
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
        ?string $metadataBackend,
    ): void {
        $schema = [];

        $primaryKeysSet = [];
        foreach ($data['columns'] as $columnName) {
            $columnMetadata = $data['column_metadata'][$columnName] ?? [];
            $dataTypes = [];
            $metadata = [];
            $isNullable = true;
            $primaryKey = false;
            $description = null;

            foreach ($columnMetadata as $meta) {
                if (str_starts_with($meta['key'], 'KBC.datatype.') && $meta['key'] !== 'KBC.datatype.nullable') {
                    $this->setDataType($meta, $dataTypes, $metadataBackend);
                } else {
                    $this->setMetadata($meta, $metadata, $description, $primaryKey, $isNullable);
                }
            }

            $isPK = $primaryKey ?: (isset($data['primary_key']) && in_array($columnName, $data['primary_key']));
            if ($isPK) {
                $primaryKeysSet[] = $columnName;
            }
            $schema[] = new ManifestOptionsSchema(
                $columnName,
                $dataTypes,
                $isNullable,
                $isPK,
                $description,
                empty($metadata) ? null : $metadata,
            );
        }

        if (isset($data['primary_key']) && count($primaryKeysSet) !== count($data['primary_key'])) {
            throw new UserException(sprintf(
                'Primary keys do not match columns. Missing columns: %s',
                implode(', ', array_diff($data['primary_key'], $primaryKeysSet)),
            ));
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
            if (is_bool($meta['value'])) {
                $value = $meta['value'] ? 'true' : 'false';
            } else {
                $value = (string) $meta['value'];
            }
            $dataTypes['base'][$key] = $value;
            $dataTypes[$backend][$key] = $value;
        }
    }

    private function setMetadata(
        array $meta,
        array &$metadata,
        ?string &$description,
        bool &$primaryKey,
        bool &$isNullable,
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

    private function deduplicateMetadata(array &$columnMetadata): void
    {
        $columnMetadata = array_values(array_reduce(
            $columnMetadata,
            /**
             * @param array<string, array<string, mixed>> $carry
             * @param array<string, mixed> $item
             * @return array<string, array<string, mixed>>
             */
            function (array $carry, array $item): array {
                if (isset($item['key']) && !isset($carry[$item['key']])) {
                    $carry[$item['key']] = $item;
                }
                return $carry;
            },
            [],
        ));
    }
}
