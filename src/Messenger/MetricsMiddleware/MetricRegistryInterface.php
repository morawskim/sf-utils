<?php

namespace mmo\sf\Messenger\MetricsMiddleware;

interface MetricRegistryInterface
{
    public function increaseSentCounter(int $count, string $destination, string $messageClass): void;

    public function observeSentDurationOperation(int|float $value, string $destination, string $messageClass): void;

    public function increaseConsumeCounter(int $count, string $destination, bool $error, string $messageClass): void;

    public function observeProcessingDurationOperation(int|float $value, string $destination, bool $error, string $messageClass): void;
}
