<?php

namespace mmo\sf\tests\Form\DataTransformer;

use mmo\sf\Form\DataTransformer\RamseyUuidToStringTransformer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RamseyUuidToStringTransformerTest extends TestCase
{
    public function provideValidUuid()
    {
        return [
            ['123e4567-e89b-12d3-a456-426655440000', Uuid::fromString('123e4567-e89b-12d3-a456-426655440000')],
        ];
    }

    /**
     * @dataProvider provideValidUuid
     */
    public function testTransform($uuidAsString, $uuidVO)
    {
        $transformer = new RamseyUuidToStringTransformer();

        $this->assertEquals($uuidAsString, $transformer->transform($uuidVO));
    }

    public function testTransformEmpty()
    {
        $transformer = new RamseyUuidToStringTransformer();

        $this->assertNull($transformer->transform(null));
    }

    public function testTransformExpectsUuid()
    {
        $transformer = new RamseyUuidToStringTransformer();

        $this->expectException(TransformationFailedException::class);

        $transformer->transform('1234');
    }

    /**
     * @dataProvider provideValidUuid
     */
    public function testReverseTransform($uuidAsString, $uuid)
    {
        $reverseTransformer = new RamseyUuidToStringTransformer();

        $this->assertEquals($uuid, $reverseTransformer->reverseTransform($uuidAsString));
    }

    public function testReverseTransformEmpty()
    {
        $reverseTransformer = new RamseyUuidToStringTransformer();

        $this->assertNull($reverseTransformer->reverseTransform(''));
    }

    public function testReverseTransformExpectsString()
    {
        $reverseTransformer = new RamseyUuidToStringTransformer();

        $this->expectException(TransformationFailedException::class);

        $reverseTransformer->reverseTransform(1234);
    }

    public function testReverseTransformExpectsValidUuidString()
    {
        $reverseTransformer = new RamseyUuidToStringTransformer();

        $this->expectException(TransformationFailedException::class);

        $reverseTransformer->reverseTransform('1234');
    }
}
