<?php

namespace mmo\sf\Messenger\MetricsMiddlewareTests;

use mmo\sf\Messenger\MetricsMiddleware\MetricRegistry\PrometheusMetricRegistry;
use mmo\sf\Messenger\MetricsMiddleware\MetricsMiddleware;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\InMemory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Contracts\Service\ServiceLocatorTrait;

class MetricMiddlewareMessageBusTest extends TestCase
{
    private CollectorRegistry $registry;
    private PrometheusMetricRegistry $collector;
    private MessageBus $bus;

    protected function setUp(): void
    {
        $this->registry = new CollectorRegistry(new InMemory());
        $this->collector = new PrometheusMetricRegistry($this->registry);



        $this->bus = new MessageBus([
            new MetricsMiddleware($this->collector),
            new SendMessageMiddleware(new SendersLocator([
                DummyMessage::class => ['async'],
            ], new class(['async' => fn () => new SyncTransport($this->bus)]) implements ContainerInterface {
                use ServiceLocatorTrait;
            })),
            new HandleMessageMiddleware(new HandlersLocator([
                DummyMessage::class => [function (DummyMessage $message) {
                    if ($message->shouldFail) {
                        throw new \RuntimeException('Failed');
                    }
                }]
            ])),
        ]);
    }

    public function testItCollectsMetricsForConsumedMessage(): void
    {
        $envelope = new Envelope(new DummyMessage());
//        $envelope = $envelope->with(new ReceivedStamp('async'));

        $this->bus->dispatch($envelope);

        $counterSample = $this->getMetric($this->registry->getMetricFamilySamples(), 'sf_messenger_processed_messages_total');
        $this->assertNotNull($counterSample);
        $this->assertCount(1, $counterSample->getSamples());
        $sample = $counterSample->getSamples()[0];
        $this->assertEquals(1, $sample->getValue());
        $this->assertEquals([
            'transport' => 'async',
            'error' => '0',
            'message' => DummyMessage::class,
        ], array_combine($counterSample->getLabelNames(), $sample->getLabelValues()));
    }

    public function testItCollectsMetricsForFailedMessage(): void
    {
        $envelope = new Envelope(new DummyMessage(true));
        $envelope = $envelope->with(new ReceivedStamp('async'));

        try {
            $this->bus->dispatch($envelope);
        } catch (\Throwable $e) {
            // expected
        }

        $counterSample = $this->getMetric($this->registry->getMetricFamilySamples(), 'sf_messenger_processed_messages_total');
        $this->assertNotNull($counterSample);
        $sample = $counterSample->getSamples()[0];
        $this->assertEquals(1, $sample->getValue());
        $this->assertEquals('1', array_combine($counterSample->getLabelNames(), $sample->getLabelValues())['error']);
    }

    public function testItCollectsHistogramMetrics(): void
    {
        $envelope = new Envelope(new DummyMessage());
        $envelope = $envelope->with(new ReceivedStamp('async'));

        $this->bus->dispatch($envelope);

        $histogramSample = $this->getMetric($this->registry->getMetricFamilySamples(), 'sf_messenger_processed_messages_duration_seconds');
        $this->assertNotNull($histogramSample);
        // Histogram has samples for buckets, sum and count
        $this->assertGreaterThan(0, count($histogramSample->getSamples()));
        
        $sumSample = null;
        foreach ($histogramSample->getSamples() as $sample) {
            if (str_ends_with($sample->getName(), '_sum')) {
                $sumSample = $sample;
                break;
            }
        }
        $this->assertNotNull($sumSample);
        $this->assertGreaterThan(0, $sumSample->getValue());

        $expectedLabels = [
            'transport' => 'async',
            'error' => '0',
            'message' => DummyMessage::class,
        ];

        foreach ($histogramSample->getSamples() as $sample) {
            $this->assertEquals($expectedLabels, array_combine($histogramSample->getLabelNames(), array_slice($sample->getLabelValues(), 0, 3)));
        }
    }

    private function getMetric(array $metrics, string $metricName): MetricFamilySamples|null
    {
        $result = null;
        foreach ($metrics as $metric) {
            if ($metric->getName() === $metricName) {
                $result = $metric;
                break;
            }
        }

        return $result;
    }
}

class DummyMessage
{
    public function __construct(public bool $shouldFail = false) {}
}
