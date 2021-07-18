<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OnlyDigits extends Constraint
{
    public $message = 'Only digits are allowed.';
}
