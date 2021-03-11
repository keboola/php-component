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

    public static function getSyncActionErrorHandler(): StreamHandler
    {
        $logHandler = new StreamHandler('php://stderr');
        $logHandler->setBubble(false);
        $logHandler->setLevel(MonologLogger::ERROR);
        $logHandler->setFormatter(new LineFormatter("%message%\n"));
        return $logHandler;
    }

    public static function getSyncActionCriticalHandler(): StreamHandler
    {
        $logHandler = new StreamHandler('php://stderr');
        $logHandler->setBubble(false);
        $logHandler->setLevel(MonologLogger::CRITICAL);
        $logHandler->setFormatter(new LineFormatter("%message% %context% %extra%\n"));
        return $logHandler;
    }

    public function __construct()
    {
        parent::__construct('php-component');

        // Add default logger to log errors in configuration, etc.
        // It will be overwritten by calling setupSyncActionLogging/setupAsyncActionLogging
        // from BaseComponent::initializeSyncActions
        $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
    }

    public function setupSyncActionLogging(): void
    {
        $criticalHandler = self::getSyncActionCriticalHandler();
        $errorHandler = self::getSyncActionErrorHandler();

        $this->setHandlers([
            $criticalHandler,
            $errorHandler,
        ]);
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
