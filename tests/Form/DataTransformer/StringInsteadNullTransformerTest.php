<?php

namespace mmo\sf\tests\Form\DataTransformer;

use DateTimeImmutable;
use mmo\sf\Form\DataTransformer\StringInsteadNullTransformer;
use PHPUnit\Framework\TestCase;

class StringInsteadNullTransformerTest extends TestCase
{
    /**
     * @dataProvider providerForTransform
     */
    public function testTransform($value): void
    {
        $transformer = new StringInsteadNullTransformer();

        $this->assertSame($value, $transformer->transform($value));
    }

    public function providerForTransform(): iterable
    {
        yield 'scalar' => [1];
        yield 'object' => [new DateTimeImmutable()];
        yield 'null' => [null];
    }

    /**
     * @dataProvider providerForReverseTransform
     *
     * @param $value
     * @param $expectedValue
     */
    public function testReverseTransform($value, $expectedValue): void
    {
        $transformer = new StringInsteadNullTransformer();

        $this->assertSame($expectedValue, $transformer->reverseTransform($value));
    }

    public function providerForReverseTransform(): iterable
    {
        yield 'scalar' => [1, 1];

        $dateTimeImmutable = new DateTimeImmutable();
        yield 'object' => [$dateTimeImmutable, $dateTimeImmutable];

        yield 'null' => [null, ''];
    }
}
