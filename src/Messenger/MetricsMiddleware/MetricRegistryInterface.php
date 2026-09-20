<?php

namespace mmo\sf\Messenger\MetricsMiddleware;

interface MetricRegistryInterface
{
    public function increaseSentCounter(int $count, string $destination): void;

    public function observeSentDurationOperation(int|float $value, string $destination): void;

    public function increaseConsumeCounter(int $count, string $destination, bool $error): void;

    public function observeProcessingDurationOperation(int|float $value, string $destination, bool $error): void;
}
