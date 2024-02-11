<?php

namespace mmo\sf\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class PerformanceSubscriber implements EventSubscriberInterface
{
    /** @var array  */
    private $data = [
        'url' => null,
        'method' => null,
    ];

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        $this->data['url'] = $event->getRequest()->getRequestUri();
        $this->data['method'] = $event->getRequest()->getRealMethod();
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        $data = $this->data;
        $data['pid'] = getmypid();
        $data['status_code'] = $event->getResponse()->getStatusCode();
        // change in future to hrtime (requires PHP 7.3)
        $duration = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        $this->logger->info(
            sprintf('The request "{method} {url}" took "%s" second.', number_format($duration, 6)),
            $data
        );
    }

    private function isMainRequest(KernelEvent $event): bool
    {
        if (method_exists($event, 'isMasterRequest')) {
            return $event->isMasterRequest();
        }

        return $event->isMainRequest();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TerminateEvent::class => ['onKernelTerminate', -1024],
            RequestEvent::class => ['onKernelRequest', -1024],
        ];
    }
}
