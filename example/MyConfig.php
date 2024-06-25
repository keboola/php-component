<?php

declare(strict_types=1);

namespace MyComponent;

use Keboola\Component\Config\BaseConfig;

class MyConfig extends BaseConfig
{
    public function getCustomSubKey(): int
    {
        $defaultValue = 0;
        return $this->getIntValue(['parameters', 'customKey', 'customSubKey'], $defaultValue);
    }
}
