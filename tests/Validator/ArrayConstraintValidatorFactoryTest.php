<?php

namespace mmo\sf\tests\Validator;

use mmo\sf\Validator\ArrayConstraintValidatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class ArrayConstraintValidatorFactoryTest extends TestCase
{
    public function testCreateValidators(): void
    {
        $validatedBy = 'foo.service';

        $validators = [
            $validatedBy => $this->createStub(ConstraintValidatorInterface::class),
        ];

        $sut = new ArrayConstraintValidatorFactory($validators);
        $instance = $sut->getInstance($this->getConstraint($validatedBy));

        $this->assertInstanceOf(ConstraintValidatorInterface::class, $instance);
    }

    private function getConstraint(string $validatedBy)
    {
        return new class($validatedBy) extends Constraint {
            /**
             * @var string
             */
            private $validatedBy;

            public function __construct(string $validatedBy)
            {
                parent::__construct();

                $this->validatedBy = $validatedBy;
            }

            public function validatedBy()
            {
                return $this->validatedBy;
            }
        };
    }
}
