<?php

namespace mmo\sf\tests\JWTAuthenticationBundle\Listener;

use DateInterval;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use mmo\sf\JWTAuthenticationBundle\Listener\CheckRevokeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

class CheckRevokeListenerTest extends TestCase
{
    /** @var string */
    const API_LOGOUT_ROUTENAME = 'api.logout';

    const TOKEN_JTI_VALUE = 'JTI-VALUE';

    private function getJwtDecodeEvent(): JWTDecodedEvent
    {
        return new JWTDecodedEvent(['jti' => self::TOKEN_JTI_VALUE, 'exp' => time() + 900]);
    }

    private function createSut(CacheInterface $mock, string $keyPrefix = '', array $requests = []): CheckRevokeListener
    {
        $requestStack = new RequestStack();

        foreach ($requests as $request) {
            $requestStack->push($request);
        }

        return new CheckRevokeListener($requestStack, $mock, self::API_LOGOUT_ROUTENAME, $keyPrefix);
    }

    /**
     * @dataProvider invalidJwtEventProvider
     *
     * @param JWTDecodedEvent $event
     */
    public function testSkipIfPayloadNotContainsJit(JWTDecodedEvent $event): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::never())
            ->method('get');

        $sut = $this->createSut($mock);
        $this->expectException(InvalidArgumentException::class);
        $sut->onJWTDecoded($event);
    }

    public function testSkipIfInvalidToken(): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::never())
            ->method('get');

        $event = $this->getJwtDecodeEvent();
        $event->markAsInvalid();

        $sut = $this->createSut($mock);
        $sut->onJWTDecoded($event);
    }

    public function testMarkAsInvalidWhenTokenHasRevoked(): void
    {
        $f = \Closure::bind(
            static function ($key, $value, $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;

                return $item;
            },
            null,
            CacheItem::class
        );

        $event = $this->getJwtDecodeEvent();
        $cache = new ArrayAdapter();
        $cache->save($f(self::TOKEN_JTI_VALUE, 1, true)->expiresAfter(new DateInterval('PT30M')));

        $sut = $this->createSut($cache);
        $sut->onJWTDecoded($event);

        $this->assertFalse($event->isValid());
    }

    public function testTokenIsValidIfNotFoundRevokeInCache(): void
    {
        $event = $this->getJwtDecodeEvent();
        $cache = new ArrayAdapter();

        $sut = $this->createSut($cache);
        $sut->onJWTDecoded($event);

        $this->assertTrue($event->isValid());
    }

    public function testSaveTokenOnLogoutRoute(): void
    {
        $event = $this->getJwtDecodeEvent();
        $cache = new ArrayAdapter();

        $sut = $this->createSut($cache, '', [new Request([], [], ['_route' => self::API_LOGOUT_ROUTENAME])]);
        $sut->onJWTDecoded($event);
        $values = $cache->getValues();

        $this->assertTrue($event->isValid());
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(self::TOKEN_JTI_VALUE, $values);
        $this->assertNotNull($values[self::TOKEN_JTI_VALUE]);
    }

    public function testSkipTokenOnNoLogoutRoute(): void
    {
        $event = $this->getJwtDecodeEvent();
        $cache = new ArrayAdapter();

        $sut = $this->createSut($cache, '', [new Request([], [], ['_route' => 'some_route_name'])]);
        $sut->onJWTDecoded($event);
        $values = $cache->getValues();

        $this->assertTrue($event->isValid());
        $this->assertCount(1, $values);
        $this->assertArrayHasKey(self::TOKEN_JTI_VALUE, $values);
        $this->assertNotNull($values[self::TOKEN_JTI_VALUE]);
    }

    public function testKeyPrefix(): void
    {
        $event = $this->getJwtDecodeEvent();
        $cache = new ArrayAdapter();

        $sut = $this->createSut($cache, 'bar-');
        $sut->onJWTDecoded($event);
        $values = $cache->getValues();

        $this->assertArrayHasKey('bar-' . self::TOKEN_JTI_VALUE, $values);
    }

    public function invalidJwtEventProvider(): iterable
    {
        yield 'empty' => [new JWTDecodedEvent([])];
        yield 'without_jit' => [new JWTDecodedEvent(['exp' => time() + 60])];
        yield 'without_exp' => [new JWTDecodedEvent(['jit' => self::TOKEN_JTI_VALUE])];
    }
}
