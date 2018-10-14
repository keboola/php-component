<?php

declare(strict_types=1);

namespace Keboola\Component\Tests;

use Keboola\Component\BaseComponent;
use Keboola\Component\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class BaseComponentTest extends TestCase
{
    public function testLoadInputStateFile(): void
    {
        putenv(sprintf(
            'KBC_DATADIR=%s',
            __DIR__ . '/fixtures/base-component-data-dir/state-file'
        ));

        $logger = new Logger();
        $baseComponent = new BaseComponent($logger);

        $inputStateFile = $baseComponent->getInputState();
        $this->assertCount(4, $inputStateFile);

        $this->assertArrayHasKey('key1', $inputStateFile);
        $this->assertEquals('value1', $inputStateFile['key1']);

        $this->assertArrayHasKey('key2', $inputStateFile);
        $this->assertEquals(2, $inputStateFile['key2']);

        $this->assertArrayHasKey('list', $inputStateFile);
        $this->assertCount(3, $inputStateFile['list']);
        $this->assertEquals('a', $inputStateFile['list'][0]);
        $this->assertEquals('b', $inputStateFile['list'][1]);
        $this->assertEquals('c', $inputStateFile['list'][2]);

        $this->assertArrayHasKey('dict', $inputStateFile);
        $this->assertCount(1, $inputStateFile['dict']);
        $this->assertArrayHasKey('key', $inputStateFile['dict']);
        $this->assertEquals('value', $inputStateFile['dict']['key']);
    }

    public function testLoadInputStateFileEmptyThrowsException(): void
    {
        putenv(sprintf(
            'KBC_DATADIR=%s',
            __DIR__ . '/fixtures/base-component-data-dir/empty-state-file'
        ));

        $logger = new Logger();

        $this->expectException(NotEncodableValueException::class);
        $this->expectExceptionMessage('Syntax error');
        new BaseComponent($logger);
    }

    public function testLoadInputStateFileUndefined(): void
    {
        putenv(sprintf(
            'KBC_DATADIR=%s',
            __DIR__ . '/fixtures/base-component-data-dir/undefined-state-file'
        ));

        $logger = new Logger();
        $baseComponent = new BaseComponent($logger);

        $this->assertEmpty($baseComponent->getInputState());
    }
}
