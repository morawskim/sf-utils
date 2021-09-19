<?php

namespace mmo\sf\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * The goal of this transformer is to fix an error when you have a form with ChoiceType and pass an empty value for this field.
 *
 * The default Symfony implementation, returns null instead of empty string (even when empty_data is set).
 * So if in your entity/DTO you expect only string value, you get error "Expected argument of type "string", "NULL" given at property path ..."
 *
 * @link https://github.com/symfony/form/blob/5.4/Extension/Core/DataTransformer/ChoiceToValueTransformer.php#L44
 */
class StringInsteadNullTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        return $value ?? '';
    }
}
