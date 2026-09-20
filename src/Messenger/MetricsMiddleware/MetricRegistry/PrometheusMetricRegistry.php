<?php

namespace mmo\sf\Messenger\MetricsMiddleware\MetricRegistry;

use mmo\sf\Messenger\MetricsMiddleware\MetricRegistryInterface;
use Prometheus\CollectorRegistry;

class PrometheusMetricRegistry implements MetricRegistryInterface
{
    public function __construct(
        private readonly CollectorRegistry $registry,
        private readonly array $sentBucketSeconds = [0.050, 0.100, 0.250, 0.500, 1, 3, 5],
        private readonly array $processedBucketsSeconds = [5, 15, 30, 60, 90, 120, 180, 300, 600],
    ) {}

    public function increaseSentCounter(int $count, string $destination): void
    {
        $this->registry
            ->getOrRegisterCounter('sf_messenger', 'sent_messages_total', 'Number of messages sent to a transport', ['destination'])
            ->incBy($count, [$destination]);
    }

    public function observeSentDurationOperation(int|float $value, string $destination): void
    {
        $this->registry
            ->getOrRegisterHistogram('sf_messenger', 'sent_messages_duration_seconds', 'Duration of send operations', ['destination'], $this->sentBucketSeconds)
            ->observe($value, [$destination]);
    }

    public function increaseConsumeCounter(int $count, string $destination, bool $error): void
    {
        $this->registry
            ->getOrRegisterCounter('sf_messenger', 'processed_messages_total', 'Number of messages processed by the consumer', ['transport', 'error'])
            ->incBy($count, [$destination, (int) $error]);
    }

    public function observeProcessingDurationOperation(int|float $value, string $destination, bool $error): void
    {
        $this->registry
            ->getOrRegisterHistogram('sf_messenger', 'processed_messages_duration_seconds', 'Duration of processing operations', ['transport', 'error'], $this->processedBucketsSeconds)
            ->observe($value, [$destination, (int) $error]);
    }
}
