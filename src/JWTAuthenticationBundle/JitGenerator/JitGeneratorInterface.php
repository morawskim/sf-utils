<?php

namespace mmo\sf\JWTAuthenticationBundle\JitGenerator;

interface JitGeneratorInterface
{
    public function generateJit(): string;
}
