<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable;

class ManifestOptionsSchemaDataType
{
    private string $type;
    private ?string $length;
    private ?string $default;

    public function __construct(string $type, ?string $length = null, ?string $default = null)
    {
        $this->type = $type;
        $this->length = $length;
        $this->default = $default;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setLength(?string $length): void
    {
        $this->length = $length;
    }

    public function setDefault(?string $default): void
    {
        $this->default = $default;
    }
}
