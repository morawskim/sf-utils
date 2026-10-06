<?php

namespace mmo\sf\Messenger\MetricsMiddleware\MetricRegistry;

use mmo\sf\Messenger\MetricsMiddleware\MetricRegistryInterface;
use Prometheus\CollectorRegistry;

class PrometheusMetricRegistry implements MetricRegistryInterface
{
    public function __construct(
        private readonly CollectorRegistry $registry,
        private readonly string $namespace = 'sf_messenger',
        private readonly array $sentBucketSeconds = [0.050, 0.100, 0.250, 0.500, 1, 3, 5],
        private readonly array $processedBucketsSeconds = [5, 15, 30, 60, 90, 120, 180, 300, 600],
    ) {}

    public function increaseSentCounter(int $count, string $destination, string $messageClass): void
    {
        $this->registry
            ->getOrRegisterCounter($this->namespace, 'sent_messages_total', 'Number of messages sent to a transport', ['destination', 'message'])
            ->incBy($count, [$destination, $messageClass]);
    }

    public function observeSentDurationOperation(int|float $value, string $destination, string $messageClass): void
    {
        $this->registry
            ->getOrRegisterHistogram($this->namespace, 'sent_messages_duration_seconds', 'Duration of send operations', ['destination', 'message'], $this->sentBucketSeconds)
            ->observe($value, [$destination, $messageClass]);
    }

    public function increaseConsumeCounter(int $count, string $destination, bool $error, string $messageClass): void
    {
        $this->registry
            ->getOrRegisterCounter($this->namespace, 'processed_messages_total', 'Number of messages processed by the consumer', ['transport', 'error', 'message'])
            ->incBy($count, [$destination, (int) $error, $messageClass]);
    }

    public function observeProcessingDurationOperation(int|float $value, string $destination, bool $error, string $messageClass): void
    {
        $this->registry
            ->getOrRegisterHistogram($this->namespace, 'processed_messages_duration_seconds', 'Duration of processing operations', ['transport', 'error', 'message'], $this->processedBucketsSeconds)
            ->observe($value, [$destination, (int) $error, $messageClass]);
    }
}
