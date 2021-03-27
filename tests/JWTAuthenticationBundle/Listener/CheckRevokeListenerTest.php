<?php

namespace mmo\sf\tests\JWTAuthenticationBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use mmo\sf\JWTAuthenticationBundle\Listener\CheckRevokeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class CheckRevokeListenerTest extends TestCase
{
    public function testSkipIfPayloadNotContainsJit(): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::never())
            ->method('get');

        $event = new JWTDecodedEvent([]);

        $sut = new CheckRevokeListener($mock);
        $sut->onJWTDecoded($event);
    }

    public function testSkipIfInvalidToken(): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::never())
            ->method('get');

        $event = new JWTDecodedEvent(['jti' => 'qqq']);
        $event->markAsInvalid();

        $sut = new CheckRevokeListener($mock);
        $sut->onJWTDecoded($event);
    }

    /**
     * @dataProvider jtiProvider
     *
     * @param string $jtiValue
     * @param string $keyPrefix
     */
    public function testMarkAsInvalidWhenTokenHasRevoked(string $jtiValue, string $keyPrefix): void
    {
        $event = new JWTDecodedEvent(['jti' => $jtiValue]);
        $cache = new ArrayAdapter();
        $cache->get($keyPrefix . $jtiValue, function () {
            return 1;
        });

        $sut = new CheckRevokeListener($cache, $keyPrefix);
        $sut->onJWTDecoded($event);

        $this->assertFalse($event->isValid());
    }

    public function testTokenIsValidIfNotFoundRevokeInCache(): void
    {
        $event = new JWTDecodedEvent(['jti' => 'foo']);
        $cache = new ArrayAdapter();

        $sut = new CheckRevokeListener($cache);
        $sut->onJWTDecoded($event);

        $this->assertTrue($event->isValid());
    }

    public function jtiProvider(): iterable
    {
        yield 'without' => ['foo', ''];
        yield 'with_prefix' => ['foo', 'bar'];
    }
}
