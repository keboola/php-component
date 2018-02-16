<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

interface ConfigInterface
{
    /**
     * @return mixed[]|null
     */
    public function getData(): ?array;

    /**
     * @param string[] $keys
     * @return mixed
     */
    public function getValueOrNull(array $keys);

    /**
     * @param string[] $keys
     * @return mixed
     */
    public function getValue(array $keys);
}
