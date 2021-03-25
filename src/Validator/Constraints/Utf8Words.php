<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Utf8Words extends Constraint
{
    public $message = 'Only letters and spaces are allowed.';
}
