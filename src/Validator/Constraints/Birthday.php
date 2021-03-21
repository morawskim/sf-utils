<?php

namespace mmo\sf\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class Birthday extends Constraint
{
    public $minAge;
    public $maxAge = 120;

    public $message = 'Invalid a birthday date.';
    public $messageMinAge = 'You must be at least {{ age }} years old.';

    public function __construct($options = null)
    {
        if (null === $options) {
            $options = [];
        }

        if (!\is_array($options)) {
            throw new UnexpectedValueException($options, 'array');
        }

        parent::__construct($options);

        if (null !== $this->minAge && null !== $this->maxAge && $this->minAge > $this->maxAge) {
            throw new InvalidArgumentException(sprintf('The "maxAge" option must be a greater than minAge "%d"', $this->minAge));
        }
    }
}
