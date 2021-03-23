<?php

namespace mmo\sf\tests\Validator\Constraints;

use mmo\sf\Validator\Constraints\BankRoutingNumber;
use mmo\sf\Validator\Constraints\BankRoutingNumberValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class BankRoutingNumberValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): BankRoutingNumberValidator
    {
        return new BankRoutingNumberValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new BankRoutingNumber());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new BankRoutingNumber());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new BankRoutingNumber());
    }

    /**
     * @dataProvider getValidBankRoutingNumbers
     *
     * @param string|int $bankRoutingNumber
     */
    public function testValidBankRoutingNumber($bankRoutingNumber): void
    {
        $this->validator->validate($bankRoutingNumber, new BankRoutingNumber());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidBankRoutingNumbers
     *
     * @param string $bankRoutingNumber
     */
    public function testInvalidBankRoutingNumber(string $bankRoutingNumber): void
    {
        $constraint = new BankRoutingNumber(['message' => 'myMessage']);

        $this->validator->validate($bankRoutingNumber, $constraint);

        $this->buildViolation('myMessage')
            ->assertRaised();
    }

    public function getValidBankRoutingNumbers(): iterable
    {
        return [
            ['021000021'],
            ['275332587'],
            [275332587],
        ];
    }

    public function getInvalidBankRoutingNumbers(): iterable
    {
        return [
            ['foo'],
            ['aaaaaaaaa'],
            ['1234567890'],
        ];
    }
}
