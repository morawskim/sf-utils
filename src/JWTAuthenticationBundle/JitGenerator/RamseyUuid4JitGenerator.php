<?php

namespace mmo\sf\JWTAuthenticationBundle\JitGenerator;

use Ramsey\Uuid\Uuid;

class RamseyUuid4JitGenerator implements JitGeneratorInterface
{
    public function generateJit(): string
    {
        return Uuid::uuid4()->toString();
    }
}
