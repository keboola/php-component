<?php

declare(strict_types=1);

namespace Keboola\Component;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JsonFileHelper
{
    public static function read(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(null, 0, null, $filePath);
        }

        $jsonEncoder = new JsonEncoder();
        $jsonContents = file_get_contents($filePath);
        return $jsonEncoder->decode($jsonContents, JsonEncoder::FORMAT);
    }

    public static function write(string $filePath, array $data, bool $formatted = true): void
    {
        $filePathDir = pathinfo($filePath, PATHINFO_DIRNAME);
        if (!is_dir($filePathDir)) {
            mkdir($filePathDir, 0777, true);
        }

        $context = [];
        if ($formatted) {
            $context = ['json_encode_options' => JSON_PRETTY_PRINT];
        }

        $jsonEncoder = new JsonEncoder();
        $result = file_put_contents(
            $filePath,
            $jsonEncoder->encode($data, JsonEncoder::FORMAT, $context)
        );

        if ($result === false) {
            throw new \ErrorException('Could not write to file "%s".');
        }
    }
}
