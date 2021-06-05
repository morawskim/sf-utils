<?php

namespace mmo\sf\tests\Serializer\Normalizer;

use mmo\sf\Serializer\Normalizer\MoneyNormalizer;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

class MoneyNormalizerTest extends TestCase
{
    /** @var MoneyNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        if (!class_exists(Serializer::class)) {
            $this->markTestSkipped('The Symfony Serializer is not available.');
        }

        $this->normalizer = new MoneyNormalizer();
    }

    public function testSupportNormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new Money(100, new Currency('USD'))));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testNormalize(): void
    {
        $this->assertEquals(
            ['amount' => '100', 'currency' => 'USD'],
            $this->normalizer->normalize(new Money(100, new Currency('USD')))
        );
    }

    public function testSupportDenormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsDenormalization(['amount' => '100', 'currency' => 'USD'], Money::class));
        $this->assertFalse($this->normalizer->supportsDenormalization([], 'stdClass'));
    }

    public function testDenormalize(): void
    {
        $denormalizedValue = ['amount' => '100', 'currency' => 'USD'];
        $money = new Money(100, new Currency('USD'));
        $this->assertTrue($money->equals($this->normalizer->denormalize($denormalizedValue, Money::class)));
    }

    public function testItDenormalizeNullToNull(): void
    {
        $this->assertNull($this->normalizer->denormalize(null, Money::class));
    }

    public function testNormalizeAndDenormalizeSymmetry(): void
    {
        $valueToNormalize = Money::EUR(100);
        $money = $this->normalizer->denormalize($this->normalizer->normalize($valueToNormalize), Money::class);

        $this->assertTrue($money->equals($valueToNormalize));
    }

    /**
     * @dataProvider providerNotNormalizableValue
     *
     * @param mixed $value
     */
    public function testInvalidDataThrowException($value): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $this->normalizer->denormalize($value, Money::class);
    }

    public function providerNotNormalizableValue(): iterable
    {
        yield 'bad_type' => ['some string'];
        yield 'array_without_required_keys' => [['foo' => 'bar']];
    }
}
