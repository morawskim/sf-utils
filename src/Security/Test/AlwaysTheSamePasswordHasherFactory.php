<?php

namespace mmo\sf\Security\Test;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AlwaysTheSamePasswordHasherFactory implements PasswordHasherFactoryInterface
{
    private PasswordHasherInterface $passwordHasher;

    public function __construct(PasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function getPasswordHasher(string|PasswordAuthenticatedUserInterface|PasswordHasherAwareInterface $user): PasswordHasherInterface
    {
        return $this->passwordHasher;
    }
}
