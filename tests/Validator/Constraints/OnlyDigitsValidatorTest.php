<?php

namespace mmo\sf\tests\Validator\Constraints;

use mmo\sf\Validator\Constraints\OnlyDigits;
use mmo\sf\Validator\Constraints\OnlyDigitsValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class OnlyDigitsValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): OnlyDigitsValidator
    {
        return new OnlyDigitsValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new OnlyDigits());

        $this->assertNoViolation();
    }

    public function testEmptyValueIsValid(): void
    {
        $this->validator->validate('', new OnlyDigits());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new OnlyDigits());
    }

    /**
     * @dataProvider getValidOnlyDigits
     *
     * @param mixed $onlyDigits
     */
    public function testValidOnlyDigits($onlyDigits): void
    {
        $this->validator->validate($onlyDigits, new OnlyDigits());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidOnlyDigits
     *
     * @param mixed $value
     */
    public function testInvalidOnlyDigits($value): void
    {
        $constraint = new OnlyDigits(['message' => 'myMessage']);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"' . $value . '"')
            ->assertRaised();
    }

    public function getValidOnlyDigits(): iterable
    {
        return [
            ['0123456'],
            ['123456'],
            [123456],
        ];
    }

    public function getInvalidOnlyDigits(): iterable
    {
        return [
            ['123a'],
            ['foo.bar'],
            ['b123'],
            [123.45],
            ['123.45'],
        ];
    }
}
