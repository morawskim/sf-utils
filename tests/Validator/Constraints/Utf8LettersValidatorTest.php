<?php

namespace mmo\sf\tests\Validator\Constraints;

use mmo\sf\Validator\Constraints\Utf8Letters;
use mmo\sf\Validator\Constraints\Utf8LettersValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class Utf8LettersValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): Utf8LettersValidator
    {
        return new Utf8LettersValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Utf8Letters());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Utf8Letters());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new Utf8Letters());
    }

    /**
     * @dataProvider getValidUtf8Word
     *
     * @param string $onlyLetters
     */
    public function testValidOnlyLetters(string $onlyLetters): void
    {
        $this->validator->validate($onlyLetters, new Utf8Letters());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidOnlyLetters
     *
     * @param string $value
     */
    public function testInvalidOnlyLetters(string $value): void
    {
        $constraint = new Utf8Letters(['message' => 'myMessage']);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"' . $value . '"')
            ->assertRaised();
    }

    public function getValidUtf8Word(): iterable
    {
        return [
            ['onlyletters'],
            ['Zażółć'],
            ['PUŚĆ'],
            ['Zwölf'],
            ['Bűzös'],
            ['WC-lé'],
            ['Любя'],
            ['いろはにほへと'],
            ['Törkylempijävongahdus'],
        ];
    }

    public function getInvalidOnlyLetters(): iterable
    {
        return [
            ['123'],
            ['foo.bar'],
        ];
    }
}
