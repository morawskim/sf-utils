# Messenger Metrics Middleware

A Symfony Messenger middleware that collects metrics about sent and processed messages.

## Features

- Records the total number of messages sent to transports.
- Records the duration of message sending operations.
- Records the total number of messages processed by consumers.
- Records the duration of message processing operations.
- Distinguishes between successful and failed message processing.
- Supports Prometheus out of the box via `PrometheusMetricRegistry`.

## Collected Metrics

If using the `PrometheusMetricRegistry`, the following metrics are exposed:

- `sf_messenger_sent_messages_total`: Counter for messages sent to a transport. Labels: `destination`.
- `sf_messenger_sent_messages_duration_seconds`: Histogram for the duration of send operations. Labels: `destination`.
- `sf_messenger_processed_messages_total`: Counter for messages processed by the consumer. Labels: `transport`, `error` (0 or 1).
- `sf_messenger_processed_messages_duration_seconds`: Histogram for the duration of processing operations. Labels: `transport`, `error` (0 or 1).

## Usage (Standalone)

This example shows how to use the middleware in a standalone Symfony Messenger setup (without the full framework).

```php
use mmo\sf\Messenger\MetricsMiddleware\MetricRegistry\PrometheusMetricRegistry;
use mmo\sf\Messenger\MetricsMiddleware\MetricsMiddleware;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Symfony\Component\Messenger\MessageBus;

// 1. Setup your Prometheus CollectorRegistry (e.g. using InMemory storage)
$prometheusRegistry = new CollectorRegistry(new InMemory());

// 2. Create the MetricRegistry adapter
$metricRegistry = new PrometheusMetricRegistry($prometheusRegistry);

// 3. Initialize the MetricsMiddleware
$metricsMiddleware = new MetricsMiddleware($metricRegistry);

// 4. Setup MessageBus with the middleware
$bus = new MessageBus([
    $metricsMiddleware,
    //other middlewares like SendMessageMiddleware and HandleMessageMiddleware
]);

// 5. Dispatch messages
$bus->dispatch(new MyMessage());

// 6. Metrics are now recorded in $prometheusRegistry
```

## Installation

```bash
composer require mmo/sf-messenger-metrics-middleware
```
