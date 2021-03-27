<?php

namespace mmo\sf\JWTAuthenticationBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use mmo\sf\JWTAuthenticationBundle\JitGenerator\JitGeneratorInterface;

class AddJitClaimListener
{
    /**
     * @var JitGeneratorInterface
     */
    private $jitGenerator;

    public function __construct(JitGeneratorInterface $jitGenerator)
    {
        $this->jitGenerator = $jitGenerator;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $data = $event->getData();
        $data['jti'] = $this->jitGenerator->generateJit();

        $event->setData($data);
    }
}
