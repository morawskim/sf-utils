<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Itin extends Constraint
{
    public $message = 'This is not a valid ITIN number.';
}
