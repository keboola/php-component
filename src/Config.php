<?php

declare(strict_types=1);

namespace Keboola\DockerApplication;

use Symfony\Component\Config\Definition\ConfigurationInterface;

abstract class Config
{
    /**
     * To be implemented by concrete class.
     *
     * @return ConfigurationInterface
     */
    abstract public function getConfigDefinition(): ConfigurationInterface;
}
