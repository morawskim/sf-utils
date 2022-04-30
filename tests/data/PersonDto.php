<?php

namespace mmo\sf\tests\data;

class PersonDto
{
    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    public static function fromValues($firstName, $lastName): self
    {
        $dto = new static();
        $dto->firstName = $firstName;
        $dto->lastName = $lastName;

        return $dto;
    }
}
