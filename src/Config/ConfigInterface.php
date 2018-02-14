<?php declare(strict_types = 1);

namespace Keboola\DockerApplication\Config;

interface ConfigInterface
{
    /**
     * @return mixed[]|null
     */
    public function getData(): ?array;

    /**
     * @return mixed|null
     */
    public function getParameters();

    /**
     * @return mixed|null
     */
    public function getStorage();

    /**
     * @return mixed|null
     */
    public function getImageParameters();

    /**
     * @return mixed|null
     */
    public function getAuthorization();

    /**
     * @return mixed|null
     */
    public function getAction();
}
