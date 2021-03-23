<?php

namespace mmo\sf\tests\Security\Test;

use mmo\sf\Security\Test\FakePasswordEncoder;
use PHPUnit\Framework\TestCase;

class FakePasswordEncoderTest extends TestCase
{
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
