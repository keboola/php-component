<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

interface ConfigInterface
{
    /**
     * @return mixed[]
     */
    public function getData(): array;

    /**
     * @return mixed[]
     */
    public function getParameters(): array;

    /**
     * @return mixed[]
     */
    public function getStorage(): array;

    /**
     * @return mixed[]
     */
    public function getImageParameters(): array;

    /**
     * @return mixed[]
     */
    public function getAuthorization(): array;

    /**
     * @return mixed[]
     */
    public function getAction(): array;
}
