<?php

namespace mmo\sf\tests\Security\Test;

use mmo\sf\Security\Test\AlwaysTheSameEncoderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class AlwaysTheSameEncoderFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $mock = $this->createMock(PasswordEncoderInterface::class);
        $mock->expects($this->exactly(2))
            ->method('encodePassword');

        $factory = new AlwaysTheSameEncoderFactory($mock);
        $encoder = new UserPasswordEncoder($factory);

        $encoder->encodePassword(new User('test1', null), 'test');
        $encoder->encodePassword($this->createUserInterface('demo'), 'test');
    }

    private function createUserInterface($username): UserInterface
    {
        return new class($username) implements UserInterface {
            /**
             * @var string
             */
            private $username;

            public function __construct(string $username)
            {
                $this->username = $username;
            }

            public function getRoles()
            {
                return ['ROLE_USER'];
            }

            public function getPassword()
            {
                return null;
            }

            public function getSalt()
            {
                return null;
            }

            public function getUsername()
            {
                return $this->username;
            }

            public function eraseCredentials()
            {
            }
        };
    }
}
