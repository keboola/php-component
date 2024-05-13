<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer\LegacyManifestConverter;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer\LegacyManifestNormalizer;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer\NewNativeTypesManifestConverter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function array_keys;
use function gettype;
use function is_array;

class ManifestOptions
{
    private string $destination;

    /** @var string[] */
    private array $primaryKeyColumns;

    /** @var string[] */
    private ?array $columns = null;

    private bool $incremental;

    /** @var mixed[][] */
    private ?array $metadata = null;

    /** @var mixed $columnMetadata */
    private $columnMetadata = null;

    private string $delimiter;

    private string $enclosure;

    /** @var ManifestOptionsSchema[]  */
    private ?array $schema = null;

    private ?string $manifestType = null;

    private ?bool $hasHeader = null;

    private ?string $description = null;

    private ?array $tableMetadata = null;

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

        return new Serializer([new LegacyManifestNormalizer($normalizer), $normalizer], $encoders);
    }

    private static function getNewNativeTypesSerializer(): Serializer
    {
        $nameConverter = new NewNativeTypesManifestConverter();
        $normalizer = new ObjectNormalizer(null, $nameConverter);
        $encoders = [new JsonEncoder()];

        return new Serializer([$normalizer], $encoders);
    }

    public function toArray(bool $legacy = true): array
    {
        $serializer = $legacy ? self::getLegacySerializer() : self::getNewNativeTypesSerializer();

        return (array) $serializer->normalize($this, null, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
    }

    public static function fromArray(array $data, bool $legacy = true): ManifestOptions
    {
        if ($legacy) {
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

    /**
     * @param string[] $primaryKeyColumns
     */
    public function setPrimaryKeyColumns(array $primaryKeyColumns): ManifestOptions
    {
        if ($this->schema) {
            foreach ($this->schema as $schema) {
                if ($schema->isPrimaryKey()) {
                    throw new OptionsValidationException(
                        'Only one of "primary_key" or "schema[].primary_key" can be defined.',
                    );
                }
            }
        }

        $this->primaryKeyColumns = $primaryKeyColumns;
        return $this;
    }

    /**
     * @param string[] $columns
     */
    public function setColumns(array $columns): ManifestOptions
    {
        if ($this->schema) {
            throw new OptionsValidationException('Cannot set columns when schema is set');
        }
        $this->columns = $columns;
        return $this;
    }

    public function addSchema(ManifestOptionsSchema $schema): ManifestOptions
    {
        if ($this->columns) {
            throw new OptionsValidationException('Cannot set schema when columns are set');
        }

        if (!empty($this->primaryKeyColumns) && $schema->isPrimaryKey()) {
            throw new OptionsValidationException(
                'Only one of "primary_key" or "schema[].primary_key" can be defined.',
            );
        }

        $this->schema[] = $schema;
        return $this;
    }

    /**
     * @param ManifestOptionsSchema[] $schemas
     */
    public function setSchema(array $schemas): ManifestOptions
    {
        if ($this->columns) {
            throw new OptionsValidationException('Cannot set schema when columns are set');
        }

        foreach ($schemas as $schema) {
            if (!empty($this->primaryKeyColumns) && $schema->isPrimaryKey()) {
                throw new OptionsValidationException(
                    'Only one of "primary_key" or "schema[].primary_key" can be defined.',
                );
            }
        }

        $this->schema = $schemas;
        return $this;
    }

    public function setIncremental(bool $incremental): ManifestOptions
    {
        $this->incremental = $incremental;
        return $this;
    }

    public function setMetadata(array $metadata): ManifestOptions
    {
        if ($this->schema) {
            throw new OptionsValidationException('Cannot set metadata when schema is set');
        }
        $this->validateMetadata($metadata);
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @param mixed $columnsMetadata
     */
    public function setColumnMetadata($columnsMetadata): ManifestOptions
    {
        if ($this->schema) {
            throw new OptionsValidationException('Cannot set column metadata when schema is set');
        }
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
        }
        $this->columnMetadata = $columnsMetadata;
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
            if ($oneKeyAndValue['key'] === 'KBC.description' && isset($this->description)) {
                throw new OptionsValidationException(
                    'Only one of "description" or "metadata.KBC.description" can be defined.',
                );
            }
        }
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function isIncremental(): bool
    {
        return $this->incremental;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @return mixed
     */
    public function getColumnMetadata()
    {
        return $this->columnMetadata;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getEnclosure(): string
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

    public function getPrimaryKeyColumns(): array
    {
        return $this->primaryKeyColumns;
    }

    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
