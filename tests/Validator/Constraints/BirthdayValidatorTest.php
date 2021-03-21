<?php

namespace mmo\sf\tests\Validator\Constraints;

use DateTimeImmutable;
use DateTimeInterface;
use mmo\sf\Validator\Constraints\Birthday;
use mmo\sf\Validator\Constraints\BirthdayValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Validation;

class BirthdayValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): BirthdayValidator
    {
        return new BirthdayValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Birthday());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Birthday());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $validator = Validation::createValidatorBuilder()->getValidator();
        $violations = $validator->validate(new \stdClass(), new Birthday());

        $this->assertGreaterThanOrEqual(1, $violations->count());
    }

    /**
     * @dataProvider dataProviderValid
     *
     * @param DateTimeInterface $date
     * @param Birthday $constraint
     */
    public function testValidBirthday(DateTimeInterface $date, Birthday $constraint): void
    {
        $validator = Validation::createValidatorBuilder()->getValidator();
        $violations = $validator->validate($date, $constraint);

        $this->assertSame(0, $violations->count());
    }

    /**
     * @dataProvider dataProviderInvalid
     *
     * @param DateTimeInterface $date
     * @param Birthday $constraint
     * @param string $validationMessage
     */
    public function testInvalidBirthday(DateTimeInterface $date, Birthday $constraint, string $validationMessage): void
    {
        $validator = Validation::createValidatorBuilder()->getValidator();
        $violations = $validator->validate($date, $constraint);

        $this->assertSame(1, $violations->count());
        $this->assertEquals($validationMessage, $violations->get(0)->getMessage());
    }

    public function dataProviderValid(): iterable
    {
        yield 'min_and_max' => [(new DateTimeImmutable('now'))->modify('-20 years'), new Birthday(['minAge' => 18, 'maxAge' => 120])];
        yield 'exactly_min' => [(new DateTimeImmutable('now'))->modify('-18 years'), new Birthday(['minAge' => 18])];
        yield 'disable_max_age' => [(new DateTimeImmutable('now'))->modify('-150 years'), new Birthday(['maxAge' => null])];
        yield 'without_min_restriction' => [(new DateTimeImmutable('now')), new Birthday()];
    }

    public function dataProviderInvalid(): iterable
    {
        yield 'too_young' => [(new DateTimeImmutable('now'))->modify('-15 years'), new Birthday(['minAge' => 18]), 'You must be at least 18 years old.'];
        yield 'too_old' => [(new DateTimeImmutable('now'))->modify('-120 years'), new Birthday(['maxAge' => 50]), 'Invalid a birthday date.'];
        yield 'future' => [(new DateTimeImmutable('now'))->modify('+5 days'), new Birthday(), 'Invalid a birthday date.'];
        yield 'exactly_max' => [(new DateTimeImmutable('now'))->modify('-120 years'), new Birthday(['maxAge' => 120]), 'Invalid a birthday date.'];
    }
}
