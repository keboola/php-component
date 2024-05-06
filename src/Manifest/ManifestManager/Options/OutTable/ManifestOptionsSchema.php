<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;

class ManifestOptionsSchema
{
    private string $name;
    /** @var ManifestOptionsSchemaDataType[] */
    private array $dataType;
    private bool $nullable;
    private bool $primaryKey;
    private ?string $description;
    private ?array $metadata;

    private const ALLOWED_DATA_TYPES_BACKEND = [
        'base', 'redshift', 'snowflake', 'synapse', 'bigquery', 'exasol',
    ];

    public function __construct(
        string $name,
        array $dataTypes,
        bool $nullable = true,
        bool $primaryKey = false,
        ?string $description = null,
        array $metadata = []
    ) {
        $this->setName($name);
        $this->setDataType($dataTypes);
        $this->nullable = $nullable;
        $this->primaryKey = $primaryKey;
        $this->setDescription($description);
        $this->setMetadata($metadata);
    }

    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'data_type' => [],
            'nullable' => $this->nullable,
            'primary_key' => $this->primaryKey,
        ];

        foreach ($this->dataType as $backendType => $dataType) {
            $result['data_type'][$backendType] = $dataType->toArray();
        }

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        if (isset($this->metadata)) {
            $result['metadata'] = $this->metadata;
        }

        return $result;
    }

    private function setDataType(array $dataTypes): void
    {
        foreach ($dataTypes as $backendType => $config) {
            if (!in_array($backendType, self::ALLOWED_DATA_TYPES_BACKEND)) {
                throw new OptionsValidationException(
                    sprintf('The "%s" backendType is not supported.', $backendType),
                );
            }
            $this->dataType[$backendType] = new ManifestOptionsSchemaDataType(
                $config['type'],
                $config['length'] ?? null,
                $config['default'] ?? null,
            );
        }
    }

    private function setName(string $name): void
    {
        if (empty($name)) {
            throw new OptionsValidationException('Name cannot be empty.');
        }
        $this->name = $name;
    }

    private function setDescription(?string $description): void
    {
        if (isset($description) && isset($this->metadata['KBC.description'])) {
            throw new OptionsValidationException(
                'Only one of "description" or "metadata.KBC.description" can be defined.',
            );
        }
        $this->description = $description;
    }

    private function setMetadata(?array $metadata): void
    {
        if (isset($this->description) && isset($metadata['KBC.description'])) {
            throw new OptionsValidationException(
                'Only one of "description" or "metadata.KBC.description" can be defined.',
            );
        }

        $this->metadata = $metadata;
    }
}
