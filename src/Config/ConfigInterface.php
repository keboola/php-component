<?php

declare(strict_types=1);

namespace Keboola\Component\Config;

interface ConfigInterface
{
    /**
     * @return mixed[]|null
     */
    public function getData(): ?array;

    /**
     * @param string[] $keys
     */
    public function getValue(array $keys, mixed $default = null): mixed;
}
