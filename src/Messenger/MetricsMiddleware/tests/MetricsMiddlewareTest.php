<?php

namespace mmo\sf\Messenger\MetricsMiddlewareTests;

use mmo\sf\Messenger\MetricsMiddleware\MetricRegistry\PrometheusMetricRegistry;
use mmo\sf\Messenger\MetricsMiddleware\MetricsMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

class MetricsMiddlewareTest extends TestCase
{
    private CollectorRegistry $prometheusRegistry;
    private PrometheusMetricRegistry $metricRegistry;
    private MetricsMiddleware $middleware;

    protected function setUp(): void
    {
        $this->prometheusRegistry = new CollectorRegistry(new InMemory());
        $this->metricRegistry = new PrometheusMetricRegistry($this->prometheusRegistry);
        $this->middleware = new MetricsMiddleware($this->metricRegistry);
    }

    public function testRecordsMetricsForSentMessage(): void
    {
        $envelope = new Envelope(new \stdClass(), [
            new SentStamp('my_transport', 'MyTransportAlias')
        ]);

        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $nextMiddleware->expects($this->once())->method('handle')->willReturn($envelope);

        $stack = new StackMiddleware(new \ArrayIterator([$this->middleware, $nextMiddleware]));
        $this->middleware->handle($envelope, $stack);

        $metrics = $this->prometheusRegistry->getMetricFamilySamples();
        
        $sentTotal = $this->findMetricByName($metrics, 'sf_messenger_sent_messages_total');
        $this->assertNotNull($sentTotal);
        $this->assertEquals(1, $sentTotal->getSamples()[0]->getValue());
        $this->assertEquals(['MyTransportAlias'], $sentTotal->getSamples()[0]->getLabelValues());

        $sentDuration = $this->findMetricByName($metrics, 'sf_messenger_sent_messages_duration_seconds');
        $this->assertNotNull($sentDuration);
    }

    #[DataProvider('providerForTestRecordsMetricsForConsumedMessage')]
    public function testRecordsMetricsForConsumedMessage(StampInterface $stamp, string $expectedTransportLabelValue): void
    {
        $envelope = new Envelope(new \stdClass(), [
            $stamp
        ]);

        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $nextMiddleware->expects($this->once())->method('handle')->willReturn($envelope);

        $stack = new StackMiddleware(new \ArrayIterator([$this->middleware, $nextMiddleware]));
        $this->middleware->handle($envelope, $stack);

        $metrics = $this->prometheusRegistry->getMetricFamilySamples();

        $processedTotal = $this->findMetricByName($metrics, 'sf_messenger_processed_messages_total');
        $this->assertNotNull($processedTotal);
        $this->assertEquals(1, $processedTotal->getSamples()[0]->getValue());
        $this->assertEquals([$expectedTransportLabelValue, '0'], $processedTotal->getSamples()[0]->getLabelValues());

        $processedDuration = $this->findMetricByName($metrics, 'sf_messenger_processed_messages_duration_seconds');
        $this->assertNotNull($processedDuration);
        // error label is set to 0
        $this->assertSame(0, $processedDuration->getSamples()[0]->getLabelValues()[1]);
    }

    public static function providerForTestRecordsMetricsForConsumedMessage(): iterable
    {
        yield 'ReceivedStamp' => [
            new ReceivedStamp('my_transport'),
            'my_transport'
        ];

        yield 'ConsumedByWorkerStamp' => [
            new ConsumedByWorkerStamp(),
            '-'
        ];
    }

    public function testRecordsMetricsForFailedConsumedMessage(): void
    {
        $envelope = new Envelope(new \stdClass(), [
            new ReceivedStamp('my_transport'),
        ]);

        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $nextMiddleware->expects($this->once())->method('handle')->willThrowException(new \Exception('Test exception'));

        $stack = new StackMiddleware(new \ArrayIterator([$this->middleware, $nextMiddleware]));

        try {
            $this->middleware->handle($envelope, $stack);
        } catch (\Exception $e) {
            $this->assertEquals('Test exception', $e->getMessage());
        }

        $metrics = $this->prometheusRegistry->getMetricFamilySamples();

        $processedTotal = $this->findMetricByName($metrics, 'sf_messenger_processed_messages_total');
        $this->assertNotNull($processedTotal);
        $this->assertEquals(1, $processedTotal->getSamples()[0]->getValue());
        $this->assertEquals(['my_transport', '1'], $processedTotal->getSamples()[0]->getLabelValues());

        $processedDuration = $this->findMetricByName($metrics, 'sf_messenger_processed_messages_duration_seconds');
        $this->assertNotNull($processedDuration);
        // error label is set to 1
        $this->assertSame(1, $processedDuration->getSamples()[0]->getLabelValues()[1]);
    }

    private function findMetricByName(array $metrics, string $name): ?\Prometheus\MetricFamilySamples
    {
        foreach ($metrics as $metric) {
            if ($metric->getName() === $name) {
                return $metric;
            }
        }
        return null;
    }
}
