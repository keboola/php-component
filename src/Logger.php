<?php

declare(strict_types=1);

namespace Keboola\Component;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use function array_filter;
use function array_values;

class Logger extends MonologLogger
{
    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(
        array $handlers = []
    ) {
        parent::__construct('php-component');

        // only keep valid handlers
        $handlers = array_filter(
            array_values($handlers),
            function ($handler) {
                return $handler instanceof HandlerInterface;
            }
        );

        // no handlers
        if (count($handlers) === 0) {
            $criticalHandler = self::getDefaultCriticalHandler();
            $errorHandler = self::getDefaultErrorHandler();
            $logHandler = self::getDefaultLogHandler();

            $handlers = [
                $criticalHandler,
                $errorHandler,
                $logHandler,
            ];
        }

        // gelf log handler
        if (count($handlers) === 1 && !($handlers[0] instanceof GelfHandler)) {
            throw new \Exception(
                'If only one handler is provided, it needs to be GelfHandler'
            );
        }

        $this->setHandlers($handlers);
    }

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
}
