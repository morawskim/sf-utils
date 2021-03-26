<?php

namespace mmo\sf\tests\Security\Test;

use LogicException;
use mmo\sf\Security\Test\MemoryUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;

class MemoryUserProviderTest extends TestCase
{
    public function testConstructor(): void
    {
        $provider = $this->createProvider();

        $user = $provider->loadUserByUsername('test');
        $this->assertEquals('foo', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testRefresh(): void
    {
        $user = new User('test', 'bar');

        $provider = $this->createProvider();

        $refreshedUser = $provider->refreshUser($user);
        $this->assertEquals('foo', $refreshedUser->getPassword());
        $this->assertEquals(['ROLE_USER'], $refreshedUser->getRoles());
    }

    public function testCreateUser(): void
    {
        $provider = new MemoryUserProvider(User::class, []);
        $provider->createUser(new User('tester', 'foo'));

        $user = $provider->loadUserByUsername('tester');
        $this->assertEquals('foo', $user->getPassword());
    }

    public function testCreateUserAlreadyExist(): void
    {
        $this->expectException(LogicException::class);
        $provider = new MemoryUserProvider(User::class, []);
        $provider->createUser(new User('test', 'foo'));
        $provider->createUser(new User('test', 'foo'));
    }

    public function testLoadUserByUsernameDoesNotExist(): void
    {
        $this->expectException(UsernameNotFoundException::class);
        $provider = new MemoryUserProvider(User::class, []);
        $provider->loadUserByUsername('tester');
    }

    protected function createProvider(): MemoryUserProvider
    {
        return new MemoryUserProvider(
            User::class,
            [new User('test', 'foo', ['ROLE_USER'])]
        );
    }
}
