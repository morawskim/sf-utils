<?php

namespace mmo\sf\tests\Form;

use mmo\sf\Form\ReplaceIfNotSubmittedListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;

class ReplaceIfNotSubmittedListenerTest extends TestCase
{
    public function testShouldReplaceValueIfSendNull(): void
    {
        $mock = $this->createMock(FormEvent::class);
        $mock->expects(self::once())
            ->method('getData')
            ->willReturn(null);
        $mock->expects(self::once())
            ->method('setData')
            ->with('foo');

        $sut = new ReplaceIfNotSubmittedListener('foo');
        $sut->preSubmit($mock);
        $sut->submit($mock);
    }

    public function testShouldNotReplaceValueIfSendSomething(): void
    {
        $mock = $this->createMock(FormEvent::class);
        $mock->expects(self::once())
            ->method('getData')
            ->willReturn('bar');
        $mock->expects(self::never())
            ->method('setData');

        $sut = new ReplaceIfNotSubmittedListener('foo');
        $sut->preSubmit($mock);
        $sut->submit($mock);
    }
}
