<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options;

class WriteTableManifestOptions
{
    /** @var string */
    private $destination;

    /** @var string[] */
    private $primaryKeyColumns;

    /** @var string[] */
    private $columns;

    /** @var bool */
    private $incremental;

    /** @var mixed[][] */
    private $metadata;

    /** @var mixed[][] */
    private $columnMetadata;

    /** @var string */
    private $delimiter;

    /** @var string */
    private $enclosure;

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
        if (isset($this->incremental)) {
            $result['incremental'] = $this->incremental;
        }
        if (isset($this->metadata)) {
            $result['metadata'] = $this->metadata;
        }
        if (isset($this->columnMetadata)) {
            $result['column_metadata'] = $this->columnMetadata;
        }
        return $result;
    }

    public function setDestination(string $destination): WriteTableManifestOptions
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @param string[] $primaryKeyColumns
     * @return WriteTableManifestOptions
     */
    public function setPrimaryKeyColumns(array $primaryKeyColumns): WriteTableManifestOptions
    {
        $this->primaryKeyColumns = $primaryKeyColumns;
        return $this;
    }

    /**
     * @param string[] $columns
     * @return WriteTableManifestOptions
     */
    public function setColumns(array $columns): WriteTableManifestOptions
    {
        $this->columns = $columns;
        return $this;
    }

    public function setIncremental(bool $incremental): WriteTableManifestOptions
    {
        $this->incremental = $incremental;
        return $this;
    }

    /**
     * @param mixed[][] $metadata
     * @return WriteTableManifestOptions
     */
    public function setMetadata(array $metadata): WriteTableManifestOptions
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @param mixed[][] $columnMetadata
     * @return WriteTableManifestOptions
     */
    public function setColumnMetadata(array $columnMetadata): WriteTableManifestOptions
    {
        $this->columnMetadata = $columnMetadata;
        return $this;
    }

    public function setDelimiter(string $delimiter): WriteTableManifestOptions
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setEnclosure(string $enclosure): WriteTableManifestOptions
    {
        $this->enclosure = $enclosure;
        return $this;
    }
}
