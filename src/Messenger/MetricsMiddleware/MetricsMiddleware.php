<?php

namespace mmo\sf\Messenger\MetricsMiddleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

class MetricsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MetricRegistryInterface $metricRegistry,
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->isConsuming($envelope)) {
            return $this->handleConsume($envelope, $stack);
        }

        return $this->handleSent($envelope, $stack);
    }

    private function isConsuming(Envelope $envelope): bool
    {
        return null !== $envelope->last(ReceivedStamp::class)
            || null !== $envelope->last(ConsumedByWorkerStamp::class);
    }

    private function handleConsume(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var ReceivedStamp|null $receivedStamp */
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        $destination = $receivedStamp?->getTransportName() ?? '-';

        $start = hrtime(true);
        $exception = null;

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (\Throwable $e) {
            $exception = $e;

            throw $e;
        } finally {
            try {
                $this->recordConsumeMetrics($destination, $start, $exception);
            } catch (\Throwable) {
            }
        }
    }

    private function recordConsumeMetrics(string $destination, int|float $start, ?\Throwable $exception): void
    {
        $error = $exception ? 1 : 0;
        $this->metricRegistry->increaseConsumeCounter(1, $destination, $error);

        $durationSeconds = (hrtime(true) - $start) / 1_000_000_000;
        $this->metricRegistry->observeProcessingDurationOperation($durationSeconds, $destination, $error);
    }

    private function handleSent(Envelope $envelope, StackInterface $stack): Envelope
    {
        $start = hrtime(true);
        $exception = null;

        try {
            $envelope = $stack->next()->handle($envelope, $stack);

            return $envelope;
        } catch (\Throwable $e) {
            $exception = $e;

            throw $e;
        } finally {
            try {
                $this->recordSentMetrics($envelope, $start, $exception);
            } catch (\Throwable) {
            }
        }
    }

    private function recordSentMetrics(Envelope $envelope, float|int $start, ?\Throwable $exception): void
    {
        $durationSeconds = (hrtime(true) - $start) / 1_000_000_000;
        $sentStamps = $envelope->all(SentStamp::class);

        foreach ($sentStamps as $stamp) {
            /** @var SentStamp $stamp */
            $destination = $stamp->getSenderAlias() ?? $stamp->getSenderClass();

            $this->metricRegistry->increaseSentCounter(1, $destination);
            $this->metricRegistry->observeSentDurationOperation($durationSeconds, $destination);
        }
    }
}
