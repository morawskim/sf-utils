<?php

namespace mmo\sf\tests\EventSubscriber;

use mmo\sf\EventSubscriber\PerformanceSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class PerformanceSubscriberTest extends TestCase
{
    /**
     * @dataProvider providerRequestResponse
     *
     * @param Request $request
     * @param Response $response
     */
    public function testLogPerformanceDetails(Request $request, Response $response): void
    {
        $logger = new TestLogger();
        $sut = new PerformanceSubscriber($logger);

        $eventRequest = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            KernelInterface::MASTER_REQUEST
        );

        $eventTerminate = new TerminateEvent(
            $this->createMock(KernelInterface::class),
            $request,
            $response
        );

        $sut->onKernelRequest($eventRequest);
        $sut->onKernelTerminate($eventTerminate);

        $this->assertTrue($logger->hasInfoRecords());
        $context = $logger->records[0]['context'];
        $this->assertContextKeys($context);
        $this->assertEquals($request->getPathInfo(), $context['url']);
        $this->assertEquals($request->getRealMethod(), $context['method']);
        $this->assertEquals($response->getStatusCode(), $context['status_code']);
    }

    /**
     * @dataProvider providerRequestResponse
     *
     * @param Request $request
     * @param Response $response
     */
    public function testSkipIfNotMasterRequest(Request $request, Response $response): void
    {
        $logger = new TestLogger();
        $sut = new PerformanceSubscriber($logger);

        $eventRequest = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            KernelInterface::SUB_REQUEST
        );

        $eventTerminate = new TerminateEvent(
            $this->createMock(KernelInterface::class),
            $request,
            $response
        );

        $sut->onKernelRequest($eventRequest);
        $sut->onKernelTerminate($eventTerminate);

        $this->assertTrue($logger->hasInfoRecords());
        $context = $logger->records[0]['context'];
        $this->assertContextKeys($context);
        $this->assertNull($context['url']);
        $this->assertNull($context['method']);
    }

    public function providerRequestResponse(): iterable
    {
        $request = Request::create('/foo/bar');
        $response = new Response('', Response::HTTP_NO_CONTENT);

        yield 'get' => [$request, $response];

        $request = Request::create('/foo', 'POST');
        $response = new Response('', Response::HTTP_BAD_REQUEST);

        yield 'post' => [$request, $response];
    }

    private function assertContextKeys(array $context): void
    {
        $this->assertArrayHasKey('url', $context);
        $this->assertArrayHasKey('method', $context);
        $this->assertArrayHasKey('status_code', $context);
        $this->assertArrayHasKey('pid', $context);
    }
}
