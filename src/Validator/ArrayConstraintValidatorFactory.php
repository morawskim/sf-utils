<?php

namespace mmo\sf\Validator;

use Symfony\Component\Validator\ConstraintValidatorFactory;

/**
 * This class is useful in testing environments.
 *
 * Validators which don't follow a convention of naming a Constraint and ConstraintValidator,
 * will not be found by the default implementation of ConstraintValidatorFactory.
 *
 * @see ConstraintValidatorFactory
 */
class ArrayConstraintValidatorFactory extends ConstraintValidatorFactory
{
    public function __construct(array $validators = [])
    {
        parent::__construct();

        $this->validators = $validators;
    }
}
