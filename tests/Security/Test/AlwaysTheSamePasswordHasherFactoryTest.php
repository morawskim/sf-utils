<?php

namespace mmo\sf\tests\Security\Test;

use mmo\sf\Security\Test\AlwaysTheSamePasswordHasherFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AlwaysTheSamePasswordHasherFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(PasswordHasherInterface::class)) {
            $this->markTestSkipped('This test requires Symfony 5.4+');
        }
    }

    public function testFactory(): void
    {
        $mock = $this->createMock(PasswordHasherInterface::class);
        $mock->expects($this->exactly(2))
            ->method('hash');

        $factory = new AlwaysTheSamePasswordHasherFactory($mock);
        $encoder = new UserPasswordHasher($factory);

        $encoder->hashPassword(new InMemoryUser('test1', null), 'test');
        $encoder->hashPassword($this->createUserInterface('demo'), 'test');
    }

    private function createUserInterface($username): PasswordAuthenticatedUserInterface
    {
        return new class($username) implements PasswordAuthenticatedUserInterface {
            /**
             * @var string
             */
            private $username;

            public function __construct(string $username)
            {
                $this->username = $username;
            }

            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function getPassword(): ?string
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

            public function getUserIdentifier(): string
            {
                return $this->getUsername();
            }
        };
    }
}
