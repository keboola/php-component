<?php

class MyConfig extends \Keboola\Component\Config\BaseConfig
{
    public function getMaximumAllowedErrorCount()
    {
        $defaultValue = 0;
        return $this->getValue(['parameters', 'errorCount', 'maximumAllowed'], $defaultValue);
    }
}
