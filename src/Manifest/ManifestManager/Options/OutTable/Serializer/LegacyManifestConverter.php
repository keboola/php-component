<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class LegacyManifestConverter extends CamelCaseToSnakeCaseNameConverter
{
    public function normalize(string $propertyName): string
    {
        if ($propertyName === 'primaryKeyColumns') {
            return 'primary_key';
        }

        return parent::normalize($propertyName);
    }

    public function denormalize(string $propertyName): string
    {
        if ($propertyName === 'primary_key') {
            return 'primaryKeyColumns';
        }
        return parent::denormalize($propertyName);
    }
}
