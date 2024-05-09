<?php

declare(strict_types=1);

namespace Keboola\Component\Manifest\ManifestManager\Options\OutTable\Serializer;

use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class LegacyManifestNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);
        /** @var ManifestOptions $object */
        if (is_object($object->getColumnMetadata())) {
            $data['column_metadata'] = (object) $data['column_metadata'];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ManifestOptions;
    }
}
