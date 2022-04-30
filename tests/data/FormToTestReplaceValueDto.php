<?php

namespace mmo\sf\tests\data;

class FormToTestReplaceValueDto
{
    public $text;

    /**
     * @var PersonDto|null
     */
    public $person;

    public static function factoryFromArray(array $array): self
    {
        $obj = new static();
        $obj->text = $array['text'];
        $obj->person = $array['person'];

        return $obj;
    }
}
