<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class BankRoutingNumber extends Constraint
{
    public $message = 'Invalid a routing number.';
}
