<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

use Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException;

class ManifestOptionsSchema
{
    private string $name;
    /** @var ManifestOptionsSchemaDataType[] */
    private ?array $dataType = null;
    private bool $nullable;
    private bool $primaryKey;
    private ?string $description;
    private ?array $metadata;

    public const ALLOWED_DATA_TYPES_BACKEND = [
        'base', 'redshift', 'snowflake', 'synapse', 'bigquery', 'exasol',
    ];

    /**
     * @param array{}|array<string, array{type: string, length?: string, default?: string}> $dataTypes
     * @param array<string, mixed>|null $metadata
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function __construct(
        string $name,
        ?array $dataTypes = null,
        bool $nullable = true,
        bool $primaryKey = false,
        ?string $description = null,
        ?array $metadata = null,
    ) {
        $this->setName($name);
        if ($dataTypes !== null) {
            $this->setDataType($dataTypes);
        }
        $this->nullable = $nullable;
        $this->primaryKey = $primaryKey;
        $this->setDescription($description);
        $this->setMetadata($metadata);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['data_type'] ?? [],
            $data['nullable'] ?? true,
            $data['primary_key'] ?? false,
            $data['description'] ?? null,
            $data['metadata'] ?? null,
        );
    }

    /**
     * @param array{}|array<string, array{type: string, length?: string, default?: string}> $dataTypes
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setDataType(array $dataTypes): void
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

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setName(string $name): void
    {
        if ($name === '') {
            throw new OptionsValidationException('Name cannot be empty.');
        }
        $this->name = $name;
    }

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setDescription(?string $description): void
    {
        if (isset($description) && isset($this->metadata['KBC.description'])) {
            throw new OptionsValidationException(
                'Only one of "description" or "metadata.KBC.description" can be defined.',
            );
        }
        $this->description = $description;
    }

    /**
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    public function setMetadata(?array $metadata): void
    {
        if (isset($this->description) && isset($metadata['KBC.description'])) {
            throw new OptionsValidationException(
                'Only one of "description" or "metadata.KBC.description" can be defined.',
            );
        }

        $this->metadata = $metadata;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDataType(): ?array
    {
        return $this->dataType;
    }

    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    public function setPrimaryKey(bool $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }
}
