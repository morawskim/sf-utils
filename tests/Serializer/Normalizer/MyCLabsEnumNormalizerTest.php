<?php

namespace mmo\sf\tests\Serializer\Normalizer;

use mmo\sf\Serializer\Normalizer\MyCLabsEnumNormalizer;
use mmo\sf\tests\data\StatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

class MyCLabsEnumNormalizerTest extends TestCase
{
    /** @var MyCLabsEnumNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        if (!class_exists(Serializer::class)) {
            $this->markTestSkipped('The Symfony Serializer is not available.');
        }

        $this->normalizer = new MyCLabsEnumNormalizer();
    }

    public function testSupportNormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(StatusEnum::PUBLISHED()));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testNormalize(): void
    {
        $this->assertEquals(StatusEnum::PUBLISHED()->getValue(), $this->normalizer->normalize(StatusEnum::PUBLISHED()));
    }

    public function testSupportDenormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsDenormalization(StatusEnum::PUBLISHED()->getValue(), StatusEnum::class));
        $this->assertFalse($this->normalizer->supportsDenormalization(StatusEnum::PUBLISHED()->getValue(), 'stdClass'));
    }

    public function testDenormalize(): void
    {
        $this->assertTrue(StatusEnum::PUBLISHED()->equals($this->normalizer->denormalize(StatusEnum::PUBLISHED()->getValue(), StatusEnum::class)));
    }

    public function testItDenormalizeNullToNull(): void
    {
        $this->assertNull($this->normalizer->denormalize(null, StatusEnum::class));
    }

    public function testInvalidDateThrowException(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $this->normalizer->denormalize('not_existing_value', StatusEnum::class);
    }
}
