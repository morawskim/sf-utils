<?php

namespace mmo\sf\tests\Translation;

use mmo\sf\Translation\FakeTranslator;
use PHPUnit\Framework\TestCase;

class FakeTranslatorTest extends TestCase
{
    public function testTranslate(): void
    {
        $translator = new FakeTranslator('en');
        $messageId = 'bar';

        $this->assertEquals('en-bar', $translator->trans($messageId));
    }

    public function testOverwriteLocale(): void
    {
        $translator = new FakeTranslator('en');
        $messageId = 'bar';

        $this->assertEquals('pl-bar', $translator->trans($messageId, [], null, 'pl'));
    }
}
