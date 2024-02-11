<?php

namespace mmo\sf\JWTAuthenticationBundle\Listener;

use DateTime;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $logoutRouteName;

    public function __construct(RequestStack $requestStack, CacheInterface $cachePool, string $logoutRouteName, string $cacheKeyPrefix = '')
    {
        $this->requestStack = $requestStack;
        $this->cachePool = $cachePool;
        $this->logoutRouteName = $logoutRouteName;
        $this->cacheKeyPrefix = $cacheKeyPrefix;
    }

    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!$event->isValid()) {
            return;
        }

        $payload = $event->getPayload();
        $jti = $payload['jti'] ?? null;
        $exp = $payload['exp'] ?? null;

        if (null === $jti || null === $exp) {
            throw new InvalidArgumentException('Payload has to contain keys jti and exp.');
        }

        $wasInCache = true;
        $key = $this->cacheKeyPrefix . $jti;
        $value = $this->cachePool->get($key, function (ItemInterface $item, &$save) use($payload, &$wasInCache) {
            $wasInCache = false;
            $request = $this->getMainRequest($this->requestStack);

            if ($request && $request->attributes->has('_route') && $request->attributes->get('_route') === $this->logoutRouteName) {
                $item->expiresAt(DateTime::createFromFormat('U', $payload['exp']));

                return 1;
            }

            $save = false;

            return null;
        });

        if ($wasInCache && $value) {
            $event->markAsInvalid();
        }
    }

    private function getMainRequest(RequestStack $requestStack): ?Request
    {
        if (method_exists($requestStack, 'getMasterRequest')) {
            return $requestStack->getMasterRequest();
        }

        return $requestStack->getMainRequest();
    }
}
