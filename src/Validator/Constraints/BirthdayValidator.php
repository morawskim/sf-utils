<?php

namespace mmo\sf\Validator\Constraints;

use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BirthdayValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Birthday) {
            throw new UnexpectedTypeException($constraint, Birthday::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $context = $this->context;
        $validator = $context->getValidator()->startContext();

        $constraints = [
            new Type(['type' => DateTimeInterface::class, 'message' => $constraint->message]),
            new LessThanOrEqual(['value' => 'now', 'message' => $constraint->message]),
        ];

        if (null !== $constraint->minAge) {
            $constraints[] = new LessThan(['value' => sprintf("-%d years", $constraint->minAge), 'message' => 'minAge']);
        }

        if (null !== $constraint->maxAge) {
            $constraints[] = new GreaterThanOrEqual(['value' => sprintf("-%d years", $constraint->maxAge), 'message' => $constraint->message]);
        }

        $contextualValidator = $validator->validate($value, $constraints);

        /** @var ConstraintViolationInterface $violation */
        foreach ($contextualValidator->getViolations() as $violation) {
            if ($violation->getMessage() === 'minAge') {
                $context->buildViolation($constraint->messageMinAge)
                    ->setParameter('{{ age }}', $this->formatValue($constraint->minAge))
                    ->addViolation();
            } else {
                $context->getViolations()->add($violation);
            }
        }
    }
}
