<?php

declare(strict_types=1);

namespace Keboola\Component\Config;

enum DatatypeSupport: string
{
    case AUTHORITATIVE = 'authoritative';
    case HINT = 'hint';
    case NONE = 'none';

    public function usingLegacyManifest(): bool
    {
        return $this === self::NONE;
    }
}
