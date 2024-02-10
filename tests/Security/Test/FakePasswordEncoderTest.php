<?php

namespace mmo\sf\tests\Security\Test;

use mmo\sf\Security\Test\FakePasswordEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;

class FakePasswordEncoderTest extends TestCase
{
    protected function setUp(): void
    {
        if (class_exists(PlaintextPasswordHasher::class)) {
            $this->markTestSkipped('This test requires Symfony 4.4');
        }
    }

    public function testPasswordValid(): void
    {
        $sut = new FakePasswordEncoder('foo-');

        $pass1 = $sut->encodePassword('test', null);

        $this->assertTrue($sut->isPasswordValid($pass1, 'test', null));
    }

    public function testPasswordNotValid(): void
    {
        $sut = new FakePasswordEncoder('foo-');

        $pass1 = $sut->encodePassword('test', null);

        $this->assertFalse($sut->isPasswordValid($pass1, 'TEST', null));
    }

    public function testPasswordValidDifferentPrefix(): void
    {
        $sut = new FakePasswordEncoder('foo-');
        $sut2 = new FakePasswordEncoder('bar-');

        $pass1 = $sut->encodePassword('test', null);

        $this->assertFalse($sut2->isPasswordValid($pass1, 'test', null));
    }

    public function testIgnoreCase(): void
    {
        $sut = new FakePasswordEncoder('foo-', true);

        $pass1 = $sut->encodePassword('test', null);

        $this->assertTrue($sut->isPasswordValid($pass1, 'TEST', null));
    }
}
