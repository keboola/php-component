<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer\LegacyManifestConverter;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer\LegacyManifestNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function is_array;

class ManifestOptions
{
    private const ALLOWED_MANIFEST_TYPES = [self::MANIFEST_TYPE_OUTPUT];
    public const MANIFEST_TYPE_OUTPUT = 'output';
    private ?string $destination = null;

    private ?bool $incremental = null;

    private ?string $delimiter = null;

    private ?string $enclosure = null;

    private ?string $deleteWhereColumn = null;

    private ?array $deleteWhereValues = null;

    private ?string $deleteWhereOperator = null;

    /** @var ManifestOptionsSchema[]  */
    private ?array $schema = null;

    private ?string $manifestType = null;

    private ?bool $hasHeader = null;

    private ?string $description = null;

    private ?array $tableMetadata = null;

    private ?array $legacyPrimaryKeys = null;

    private static function getLegacySerializer(): Serializer
    {
        $normalizer = new ObjectNormalizer(
            null,
            new LegacyManifestConverter(),
            null,
            null,
            null,
            null,
            [AbstractObjectNormalizer::SKIP_NULL_VALUES => true],
        );
        $encoders = [new JsonEncoder()];

        return new Serializer([new LegacyManifestNormalizer, $normalizer], $encoders);
    }

    private static function getNewNativeTypesSerializer(): Serializer
    {
        $normalizer = new ObjectNormalizer(
            null,
            new CamelCaseToSnakeCaseNameConverter(),
            null,
            null,
            null,
            null,
            [AbstractObjectNormalizer::SKIP_NULL_VALUES => true],
        );
        $encoders = [new JsonEncoder()];

        return new Serializer([$normalizer], $encoders);
    }

    public function toArray(bool $legacy = true): array
    {
        $serializer = $legacy ? self::getLegacySerializer() : self::getNewNativeTypesSerializer();
        if ($this->getLegacyPrimaryKeys() !== null) {
            $serializer = self::getLegacySerializer();
        }

        return (array) $serializer->normalize($this, null, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
    }

    public static function fromArray(array $data): ManifestOptions
    {
        if (!isset($data['manifest_type'])) {
            $serializer = self::getLegacySerializer();
        } else {
            $serializer = self::getNewNativeTypesSerializer();
        }

        /** @var ManifestOptions $manifestOptions */
        $manifestOptions = $serializer->denormalize($data, self::class);

        if (isset($data['schema']) && is_array($data['schema'])) {
            $schemas = [];
            foreach ($data['schema'] as $schemaData) {
                $schemas[] = ManifestOptionsSchema::fromArray($schemaData);
            }
            $manifestOptions->setSchema($schemas);
        }

        return $manifestOptions;
    }

    public function setDestination(string $destination): ManifestOptions
    {
        $this->destination = $destination;
        return $this;
    }

    public function addSchema(ManifestOptionsSchema $schema): ManifestOptions
    {
        $this->schema[] = $schema;
        return $this;
    }

    /**
     * @param ManifestOptionsSchema[] $schemas
     */
    public function setSchema(array $schemas): ManifestOptions
    {
        $this->schema = $schemas;
        return $this;
    }

    public function setIncremental(bool $incremental): ManifestOptions
    {
        $this->incremental = $incremental;
        return $this;
    }

    public function setDelimiter(string $delimiter): ManifestOptions
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setEnclosure(string $enclosure): ManifestOptions
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    public function setManifestType(string $manifestType): ManifestOptions
    {
        if (!in_array($manifestType, self::ALLOWED_MANIFEST_TYPES, true)) {
            throw new OptionsValidationException(sprintf(
                'Manifest type "%s" is not allowed, allowed types are: %s',
                $manifestType,
                implode(', ', self::ALLOWED_MANIFEST_TYPES),
            ));
        }

        $this->manifestType = $manifestType;
        return $this;
    }

    public function setHasHeader(bool $hasHeader): ManifestOptions
    {
        $this->hasHeader = $hasHeader;
        return $this;
    }

    public function setDescription(string $description): ManifestOptions
    {
        $this->description = $description;
        return $this;
    }

    public function setTableMetadata(array $tableMetadata): ManifestOptions
    {
        $this->tableMetadata = $tableMetadata;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function isIncremental(): ?bool
    {
        return $this->incremental;
    }

    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    public function getEnclosure(): ?string
    {
        return $this->enclosure;
    }

    public function getManifestType(): ?string
    {
        return $this->manifestType;
    }

    public function getHasHeader(): ?bool
    {
        return $this->hasHeader;
    }

    public function getTableMetadata(): ?array
    {
        return $this->tableMetadata;
    }

    public function getSchema(): ?array
    {
        return $this->schema;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setColumns(array $columns): ManifestOptions
    {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated and will be removed in a future version. '
            . 'Use addSchema() instead.',
            E_USER_DEPRECATED,
        );

        foreach ($columns as $column) {
            $this->addSchema(new ManifestOptionsSchema($column, []));
        }
        return $this;
    }

    /**
     * @param array|object $columnsMetadata
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setColumnMetadata($columnsMetadata): ManifestOptions
    {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated and will be removed in a future version. '
            . 'Use ManifestOptionsSchema::setMetadata() instead.',
            E_USER_DEPRECATED,
        );

        foreach ($columnsMetadata as $columnName => $columnMetadata) {
            if (!is_array($columnMetadata)) {
                throw new OptionsValidationException('Each column metadata item must be an array');
            }
            if (!is_string($columnName)) {
                throw new OptionsValidationException('Each column metadata item must have string key');
            }

            try {
                $this->validateMetadata($columnMetadata);
            } catch (OptionsValidationException $e) {
                throw new OptionsValidationException(sprintf('Column "%s": %s', $columnName, $e->getMessage()), 0, $e);
            }

            if ($this->schema === null) {
                throw new OptionsValidationException('Set schema (or columns) first.');
            }

            foreach ($this->schema as $schema) {
                if ($schema->getName() === $columnName) {
                    foreach ($columnMetadata as $metadata) {
                        /** @var array{key: string, value: string} $metadata */
                        $schema->setMetadata([$metadata['key'] => $metadata['value']]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setMetadata(array $metadata): ManifestOptions
    {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated and will be removed in a future version. '
            . 'Use setTableMetadata() instead.',
            E_USER_DEPRECATED,
        );

        $this->validateMetadata($metadata);

        $tableMetadata = [];
        foreach ($metadata as $meta) {
            $tableMetadata[$meta['key']] = $meta['value'];
        }
        $this->setTableMetadata($tableMetadata);
        return $this;
    }

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setPrimaryKeyColumns(array $primaryKeyColumns): ManifestOptions
    {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated and will be removed in a future version. '
            . 'Use ManifestOptionsSchema::setPrimaryKey() instead.',
            E_USER_DEPRECATED,
        );

        if ($this->schema === null) {
            throw new OptionsValidationException('Set schema (or columns) first.');
        }

        foreach ($this->schema as $schema) {
            if (in_array($schema->getName(), $primaryKeyColumns)) {
                $schema->setPrimaryKey(true);
            }
        }
        return $this;
    }

    /**
     * @param mixed $metadata
     * @throws OptionsValidationException
     */
    private function validateMetadata($metadata): void
    {
        if (!is_array($metadata)) {
            throw new OptionsValidationException('Metadata must be an array');
        }
        foreach ($metadata as $key => $oneKeyAndValue) {
            if (!is_array($oneKeyAndValue)) {
                throw new OptionsValidationException(sprintf(
                    'Metadata item #%s must be an array, found "%s"',
                    $key,
                    gettype($oneKeyAndValue),
                ));
            }
            $keys = array_keys($oneKeyAndValue);
            sort($keys);
            if ($keys !== ['key', 'value']) {
                throw new OptionsValidationException(sprintf(
                    'Metadata item #%s must have only "key" and "value" keys',
                    $key,
                ));
            }
        }
    }

    public function getDeleteWhereColumn(): ?string
    {
        return $this->deleteWhereColumn;
    }

    public function getDeleteWhereValues(): ?array
    {
        return $this->deleteWhereValues;
    }

    public function getDeleteWhereOperator(): ?string
    {
        return $this->deleteWhereOperator;
    }

    public function setDeleteWhereColumn(?string $deleteWhereColumn): ManifestOptions
    {
        $this->deleteWhereColumn = $deleteWhereColumn;
        return $this;
    }

    public function setDeleteWhereValues(?array $deleteWhereValues): ManifestOptions
    {
        $this->deleteWhereValues = $deleteWhereValues;
        return $this;
    }

    public function setDeleteWhereOperator(?string $deleteWhereOperator): ManifestOptions
    {
        $this->deleteWhereOperator = $deleteWhereOperator;
        return $this;
    }

    public function setLegacyPrimaryKeys(?array $primaryKey): ManifestOptions
    {
        $this->legacyPrimaryKeys = $primaryKey;
        return $this;
    }

    public function getLegacyPrimaryKeys(): ?array
    {
        return $this->legacyPrimaryKeys;
    }
}
