<?php

declare(strict_types=1);

namespace Keboola\Component\Logger;

interface AsyncActionLogging
{
    public function setupAsyncActionLogging(): void;
}
