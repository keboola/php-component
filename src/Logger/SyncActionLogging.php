<?php

declare(strict_types=1);

namespace Keboola\Component\Logger;

interface SyncActionLogging
{
    /**
     * Sync actions MUST NOT output anything to stdout
     */
    public function setupSyncActionLogging(): void;
}
