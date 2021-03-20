<?php

namespace mmo\sf\tests\Validator\Constraints;

use mmo\sf\Validator\Constraints\Itin;
use mmo\sf\Validator\Constraints\ItinValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ItinValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ItinValidator
    {
        return new ItinValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Itin());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Itin());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new Itin());
    }

    /**
     * @dataProvider getValidItinNumbers
     */
    public function testValidItin(string $itin): void
    {
        $this->validator->validate($itin, new Itin());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidItinNumbers
     */
    public function testInvalidItin(string $itin): void
    {
        $constraint = new Itin(['message' => 'myMessage']);

        $this->validator->validate($itin, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"' . $itin . '"')
            ->assertRaised();
    }

    public function getValidItinNumbers(): iterable
    {
        return [
            ['998-90-1524'],
            ['990-84-1899'],
            ['987-92-3044'],

            ['971904783'],
        ];
    }

    public function getInvalidItinNumbers(): iterable
    {
        return [
            ['foo'],
            ['987-32-3044'],
            ['198-90-1524'],
        ];
    }
}
