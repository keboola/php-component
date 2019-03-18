<?php

declare(strict_types=1);

namespace Keboola\Component;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger implements Logger\SyncActionLogging, Logger\AsyncActionLogging
{
    public static function getDefaultErrorHandler(): StreamHandler
    {
        $errorHandler = new StreamHandler('php://stderr');
        $errorHandler->setBubble(false);
        $errorHandler->setLevel(MonologLogger::WARNING);
        $errorHandler->setFormatter(new LineFormatter("%message%\n"));
        return $errorHandler;
    }

    public function __construct()
    {
        parent::__construct('php-component');
    }

    public static function getDefaultLogHandler(): StreamHandler
    {
        $logHandler = new StreamHandler('php://stdout');
        $logHandler->setBubble(false);
        $logHandler->setLevel(MonologLogger::INFO);
        $logHandler->setFormatter(new LineFormatter("%message%\n"));
        return $logHandler;
    }

    public static function getDefaultCriticalHandler(): StreamHandler
    {
        $handler = new StreamHandler('php://stderr');
        $handler->setBubble(false);
        $handler->setLevel(MonologLogger::CRITICAL);
        $handler->setFormatter(new LineFormatter("[%datetime%] %level_name%: %message% %context% %extra%\n"));
        return $handler;
    }

    public function setupSyncActionLogging(): void
    {
        $this->setHandlers([]);
    }

    public function setupAsyncActionLogging(): void
    {
        $criticalHandler = self::getDefaultCriticalHandler();
        $errorHandler = self::getDefaultErrorHandler();
        $logHandler = self::getDefaultLogHandler();

        $this->setHandlers([
            $criticalHandler,
            $errorHandler,
            $logHandler,
        ]);
    }
}
