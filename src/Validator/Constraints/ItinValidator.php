<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * ITIN number validator
 *
 * Based on itin-validator created by uphold
 *
 * @link https://github.com/uphold/itin-validator/blob/master/src/index.js
 */
class ItinValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Itin) {
            throw new UnexpectedTypeException($constraint, Itin::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;

        if (1 !== preg_match('/^(9\d{2})[- ]?((7[\d]|8[0-8])|(9[0-2])|(9[4-9]))[- ]?(\d{4})$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
