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

    public function toArray(): array
    {
        $result = [
            'type' => $this->type,
        ];

        if (isset($this->length)) {
            $result['length'] = $this->length;
        }

        if (isset($this->default)) {
            $result['default'] = $this->default;
        }

        return $result;
    }
}
