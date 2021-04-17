<?php

namespace mmo\sf\tests\Form\DataTransformer;

use mmo\sf\Form\DataTransformer\ValueToMyCLabsEnumTransformer;
use mmo\sf\tests\data\StatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ValueToMyCLabsEnumTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $transformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $this->assertEquals(StatusEnum::PUBLISHED()->getValue(), $transformer->transform(StatusEnum::PUBLISHED()));
    }

    public function testTransformEmpty(): void
    {
        $transformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $this->assertNull($transformer->transform(null));
    }

    public function testTransformExpectsEnum(): void
    {
        $transformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $this->expectException(TransformationFailedException::class);

        $transformer->transform('1234');
    }

    public function testReverseTransform(): void
    {
        $reverseTransformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $value = $reverseTransformer->reverseTransform(StatusEnum::PUBLISHED()->getValue());
        $this->assertInstanceOf(StatusEnum::class, $value);
        $this->assertTrue(StatusEnum::PUBLISHED()->equals($value));
    }

    public function testReverseTransformEmpty(): void
    {
        $reverseTransformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $this->assertNull($reverseTransformer->reverseTransform(''));
        $this->assertNull($reverseTransformer->reverseTransform(null));
    }

    public function testReverseTransformExpectsString(): void
    {
        $reverseTransformer = new ValueToMyCLabsEnumTransformer(StatusEnum::class);

        $this->expectException(TransformationFailedException::class);

        $reverseTransformer->reverseTransform(1234);
    }
}
