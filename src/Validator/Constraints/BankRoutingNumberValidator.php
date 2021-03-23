<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Bank routing number validator
 *
 * Based on bank-routing-number-validator created by DrShaffopolis
 *
 * @link https://github.com/DrShaffopolis/bank-routing-number-validator
 */
class BankRoutingNumberValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BankRoutingNumber) {
            throw new UnexpectedTypeException($constraint, Itin::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;

        if (9 !== strlen($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        if (!ctype_digit($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        if (!$this->checkFirstTwoChars($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }


        if (!$this->checkChecksum($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }
    }

    private function checkFirstTwoChars(string $value)
    {
        $firstTwo = (int) substr($value, 0, 2);

        return (0 <= $firstTwo && $firstTwo <= 12)
            || (21 <= $firstTwo && $firstTwo <= 32)
            || (61 <= $firstTwo && $firstTwo <= 72)
            || $firstTwo === 80;
    }

    private function checkChecksum($value)
    {
        $weights = [3, 7 ,1];
        $sum = 0;
        for ($i=0 ; $i<8; $i++) {
            $sum += (int) ($value[$i]) * $weights[$i % 3];
        }

        return (10 - ($sum % 10)) % 10 === (int) ($value[8]);
    }
}
