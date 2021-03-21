<?php

namespace mmo\sf\tests\Translation;

use mmo\sf\Translation\FakeTranslator;
use PHPUnit\Framework\TestCase;

class FakeTranslatorTest extends TestCase
{
    public function testTranslate(): void
    {
        $translator = new FakeTranslator('foo');
        $messageId = 'bar';

        $this->assertEquals('foo-bar', $translator->trans($messageId));
    }
}
