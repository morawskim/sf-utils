<?php

namespace mmo\sf\tests\JWTAuthenticationBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use mmo\sf\JWTAuthenticationBundle\JitGenerator\JitGeneratorInterface;
use mmo\sf\JWTAuthenticationBundle\Listener\AddJitClaimListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;

class AddJitClaimListenerTest extends TestCase
{
    public function eventProvider(): iterable
    {
        if (class_exists(User::class)) {
            yield 'basic' => [new JWTCreatedEvent([], new User('test', 'foo')), 1];
            yield 'merge_array' => [new JWTCreatedEvent(['key' => 'value'], new User('test', 'foo')), 2];
        } else {
            yield 'basic' => [new JWTCreatedEvent([], new InMemoryUser('test', 'foo')), 1];
            yield 'merge_array' => [new JWTCreatedEvent(['key' => 'value'], new InMemoryUser('test', 'foo')), 2];
        }
    }

    /**
     * @dataProvider eventProvider
     *
     * @param JWTCreatedEvent $event
     * @param int $expectedArraySize
     */
    public function testAddJitToken(JWTCreatedEvent $event, int $expectedArraySize): void
    {
        $jitGenerator = $this->createMock(JitGeneratorInterface::class);
        $jitGenerator->expects(self::once())
            ->method('generateJit')
            ->willReturn('bar');

        $sut = new AddJitClaimListener($jitGenerator);
        $sut->onJWTCreated($event);

        $this->assertCount($expectedArraySize, $event->getData());
        $this->assertArrayHasKey('jti', $event->getData());
        $this->assertEquals('bar', $event->getData()['jti']);
    }
}
