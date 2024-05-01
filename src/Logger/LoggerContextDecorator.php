<?php

namespace mmo\sf\Logger;

use Psr\Log\LoggerInterface;

class LoggerContextDecorator implements LoggerInterface
{
    private LoggerInterface $logger;
    private array $context;

    private function __construct(LoggerInterface $logger, array $context)
    {
        $this->logger = $logger;
        $this->context = $context;
    }

    public static function decorate(LoggerInterface $logger, array $context): LoggerInterface
    {
        return new self($logger, $context);
    }

    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, array_merge($this->context, $context));
    }

    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, array_merge($this->context, $context));
    }

    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, array_merge($this->context, $context));
    }

    public function error($message, array $context = []): void
    {
        $this->logger->error($message, array_merge($this->context, $context));
    }

    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, array_merge($this->context, $context));
    }

    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, array_merge($this->context, $context));
    }

    public function info($message, array $context = []): void
    {
        $this->logger->info($message, array_merge($this->context, $context));
    }

    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, array_merge($this->context, $context));
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, array_merge($this->context, $context));
    }
}
