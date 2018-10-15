<?php

declare(strict_types=1);

namespace Keboola\Component;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JsonFileHelper
{
    public function read(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(null, 0, null, $filePath);
        }

        $jsonEncoder = new JsonEncoder();
        $jsonContents = file_get_contents($filePath);
        return $jsonEncoder->decode($jsonContents, JsonEncoder::FORMAT);
    }

    public function write(string $filePath, array $data, array $context = []): void
    {
        $jsonEncoder = new JsonEncoder();
        file_put_contents(
            $filePath,
            $jsonEncoder->encode($data, JsonEncoder::FORMAT, $context)
        );
    }
}
