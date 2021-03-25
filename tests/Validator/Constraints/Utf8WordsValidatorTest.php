<?php

namespace mmo\sf\tests\Validator\Constraints;

use mmo\sf\Validator\Constraints\Utf8Words;
use mmo\sf\Validator\Constraints\Utf8WordsValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class Utf8WordsValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): Utf8WordsValidator
    {
        return new Utf8WordsValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Utf8Words());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Utf8Words());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), new Utf8Words());
    }

    /**
     * @dataProvider getValidUtf8Word
     *
     * @param string $words
     */
    public function testValidWords(string $words): void
    {
        $this->validator->validate($words, new Utf8Words());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidOnlyLetters
     *
     * @param string $value
     */
    public function testInvalidWords(string $value): void
    {
        $constraint = new Utf8Words(['message' => 'myMessage']);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"' . $value . '"')
            ->assertRaised();
    }

    public function getValidUtf8Word(): iterable
    {
        return [
            ['only letters'],
            ['Zażółć gęślą'],
            ['PUŚĆ DŁOŃ'],
            ['Zwölf große'],
            ['Bűzös WC-lé'],
            ['Любя съешь'],
            ['いろはにほへと 有為の奥山'],
            ['Törkylempijävongahdus', "sioux'ta"],
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
