<?php

namespace mmo\sf\JWTAuthenticationBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CheckRevokeListener
{
    /**
     * @var CacheInterface
     */
    private $cachePool;

    /**
     * @var string
     */
    private $cacheKeyPrefix;

    public function __construct(CacheInterface $cachePool, string $cacheKeyPrefix = '')
    {
        $this->cachePool = $cachePool;
        $this->cacheKeyPrefix = $cacheKeyPrefix;
    }

    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!$event->isValid()) {
            return;
        }

        $payload = $event->getPayload();
        $jti = $payload['jti'] ?? null;

        if (null === $jti) {
            return;
        }

        $key = $this->cacheKeyPrefix . $jti;
        $value = $this->cachePool->get($key, function (ItemInterface $item, &$save) {
            $save = false;

            return null;
        });

        if (null !== $value) {
            $event->markAsInvalid();
        }
    }
}
