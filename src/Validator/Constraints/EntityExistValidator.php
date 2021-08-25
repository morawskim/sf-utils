<?php

namespace mmo\sf\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Based on happyr/entity-exists-validation-constraint
 */
class EntityExistValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof EntityExist) {
            throw new \LogicException(\sprintf('You can only pass %s constraint to this validator.', EntityExist::class));
        }

        if (empty($constraint->entity)) {
            throw new \LogicException(\sprintf('Must set "entity" on "%s" validator', EntityExist::class));
        }

        $data = $this->entityManager->getRepository($constraint->entity)->findOneBy([
            $constraint->property => $value,
        ]);

        if (null === $data || !$this->checkEntity($data)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%entity%', $constraint->entity)
                ->setParameter('%property%', $constraint->property)
                ->setParameter('%value%', $value)
                ->addViolation();
        }
    }

    protected function checkEntity(object $entity): bool
    {
        return true;
    }
}
