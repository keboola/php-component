<?php declare(strict_types = 1);

namespace MyComponent;

class MyConfig extends \Keboola\Component\Config\BaseConfig
{
    public function getMaximumAllowedErrorCount(): int
    {
        $defaultValue = 0;
        return $this->getValue(['parameters', 'errorCount', 'maximumAllowed'], $defaultValue);
    }
}
