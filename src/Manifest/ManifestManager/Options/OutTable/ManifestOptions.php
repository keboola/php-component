<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;
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

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $result = [];
        if (isset($this->destination)) {
            $result['destination'] = $this->destination;
        }
        if (isset($this->primaryKeyColumns)) {
            $result['primary_key'] = $this->primaryKeyColumns;
        }
        if (isset($this->delimiter)) {
            $result['delimiter'] = $this->delimiter;
        }
        if (isset($this->enclosure)) {
            $result['enclosure'] = $this->enclosure;
        }
        if (isset($this->columns)) {
            $result['columns'] = $this->columns;
        }
        if (isset($this->schema)) {
            foreach ($this->schema as $schema) {
                $result['schema'][] = $schema->toArray();
            }
        }
        if (isset($this->incremental)) {
            $result['incremental'] = $this->incremental;
        }
        if (isset($this->metadata)) {
            $result['metadata'] = $this->metadata;
        }
        if (isset($this->columnMetadata)) {
            $result['column_metadata'] = $this->columnMetadata;
        }
        if (isset($this->manifestType)) {
            $result['manifest_type'] = $this->manifestType;
        }
        if (isset($this->hasHeader)) {
            $result['has_header'] = $this->hasHeader;
        }
        if (isset($this->description)) {
            $result['description'] = $this->description;
        }
        if (isset($this->tableMetadata)) {
            $result['table_metadata'] = $this->tableMetadata;
        }
        return $result;
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
        $this->schema[] = $schema;
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
}
