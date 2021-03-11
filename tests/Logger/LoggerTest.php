<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Logger;

use Keboola\Component\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testDefaultHandler(): void
    {
        $logger = new Logger();
        $logger->debug('test');

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);

        /** @var StreamHandler $streamHandler */
        $streamHandler = $handlers[0];
        $this->assertSame('php://stderr', $streamHandler->getUrl());
        $this->assertSame(Logger::DEBUG, $streamHandler->getLevel());
    }

    public function testSetupSyncActionLogging(): void
    {
        $logger = new Logger();
        $logger->debug('test');
        $logger->setupSyncActionLogging();

        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);

        /** @var StreamHandler $streamHandler1 */
        $streamHandler1 = $handlers[0];
        $this->assertSame('php://stderr', $streamHandler1->getUrl());
        $this->assertSame(Logger::CRITICAL, $streamHandler1->getLevel());

        /** @var StreamHandler $streamHandler2 */
        $streamHandler2 = $handlers[1];
        $this->assertSame('php://stderr', $streamHandler2->getUrl());
        $this->assertSame(Logger::ERROR, $streamHandler2->getLevel());
    }

    public function testSetupAsyncActionLogging(): void
    {
        $logger = new Logger();
        $logger->debug('test');
        $logger->setupAsyncActionLogging();

        $handlers = $logger->getHandlers();
        $this->assertCount(3, $handlers);
    }
}
