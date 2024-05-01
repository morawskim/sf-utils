<?php

namespace mmo\sf\tests\Logger;

use mmo\sf\Logger\LoggerContextDecorator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class LoggerContextDecoratorTest extends TestCase
{
    public function testMergeEmptyContext(): void
    {
        $logger = LoggerContextDecorator::decorate(
            $innerLogger = new TestLogger(),
            []
        );

        $logger->info('foo', ['bar' => 'baz']);

        $this->assertTrue($innerLogger->hasInfoRecords());
        $this->assertCount(1, $innerLogger->records);
        $this->assertTrue($innerLogger->hasInfo('foo'));
        $this->assertEquals(['bar' => 'baz'], $innerLogger->records[0]['context']);
    }

    public function testOverwriteContextKey(): void
    {
        $logger = LoggerContextDecorator::decorate(
            $innerLogger = new TestLogger(),
            ['foo' => 'bar', 'requestId' => '1234567890']
        );

        $logger->info('Some message', ['foo' => 'baz']);

        $this->assertTrue($innerLogger->hasInfoRecords());
        $this->assertCount(1, $innerLogger->records);
        $this->assertTrue($innerLogger->hasInfo('Some message'));
        $this->assertEquals(['foo' => 'baz', 'requestId' => '1234567890'], $innerLogger->records[0]['context']);
    }

    public function testNestedAnotherLoggerContextDecorator(): void
    {
        $logger = LoggerContextDecorator::decorate(
            LoggerContextDecorator::decorate($innerLogger = new TestLogger(), ['qwe' => 'asd']),
            ['foo' => 'bar', 'requestId' => '1234567890']
        );

        $logger->info('Some message', ['foo' => 'baz']);

        $this->assertTrue($innerLogger->hasInfoRecords());
        $this->assertCount(1, $innerLogger->records);
        $this->assertTrue($innerLogger->hasInfo('Some message'));
        $this->assertEquals(
            ['foo' => 'baz', 'requestId' => '1234567890', 'qwe' => 'asd'],
            $innerLogger->records[0]['context']
        );
    }
}
