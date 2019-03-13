<?php

declare(strict_types=1);

namespace Keboola\Component\Exception;

use Exception;

class BaseComponentException extends Exception
{
    public static function invalidSyncAction(string $action): self
    {
        return new self(sprintf(
            'Unknown sync action "%s", method does not exist in class',
            $action
        ));
    }

    public static function runCannotBeSyncAction(): self
    {
        return new self('"run" cannot be a sync action');
    }
}
