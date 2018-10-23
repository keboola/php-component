<?php

declare(strict_types=1);

namespace Keboola\Component;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JsonHelper
{
    public static function decode(string $json): array
    {
        $jsonEncoder = new JsonEncoder();
        return $jsonEncoder->decode($json, JsonEncoder::FORMAT);
    }

    public static function encode(array $data, bool $formatted = true): string
    {
        $context = [];
        if ($formatted) {
            $context = ['json_encode_options' => JSON_PRETTY_PRINT];
        }

        $jsonEncoder = new JsonEncoder();
        return (string) $jsonEncoder->encode($data, JsonEncoder::FORMAT, $context);
    }

    public static function readFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(null, 0, null, $filePath);
        }

        return self::decode(file_get_contents($filePath));
    }

    public static function writeFile(string $filePath, array $data, bool $formatted = true): void
    {
        $filePathDir = pathinfo($filePath, PATHINFO_DIRNAME);
        if (!is_dir($filePathDir)) {
            mkdir($filePathDir, 0777, true);
        }

        $result = file_put_contents(
            $filePath,
            self::encode($data, $formatted)
        );

        if ($result === false) {
            throw new \ErrorException('Could not write to file "%s".');
        }
    }
}
