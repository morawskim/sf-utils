<?php

namespace mmo\sf\Security\Test;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * AlwaysTheSameEncoderFactory is useful in integration tests with combination of `UserPasswordEncoder`.
 *
 * No matter which implementation of UserInterface you pass
 * will always be used the same password encoder injected via constructor.
 *
 * @see UserPasswordEncoder
 */
class AlwaysTheSameEncoderFactory implements EncoderFactoryInterface
{
    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getEncoder($user): PasswordEncoderInterface
    {
        return $this->passwordEncoder;
    }
}
