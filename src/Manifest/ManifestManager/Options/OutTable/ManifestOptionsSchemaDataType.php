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
}
