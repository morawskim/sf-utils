<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Based on happyr/entity-exists-validation-constraint
 *
 * @Annotation
 */
class EntityExist extends Constraint
{
    public $message = 'Entity "%entity%" with property "%property%": "%value%" does not exist.';
    public $property = 'id';
    public $entity;
}
