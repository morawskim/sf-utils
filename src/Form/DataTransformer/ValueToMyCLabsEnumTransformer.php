<?php

namespace mmo\sf\Form\DataTransformer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use UnexpectedValueException;

class ValueToMyCLabsEnumTransformer implements DataTransformerInterface
{
    /** @var string|Enum */
    private $enumClass;

    public function __construct(string $enumClass)
    {
        if (!is_a($enumClass, Enum::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not an instance of "%s"',
                    $enumClass,
                    Enum::class
                )
            );
        }

        $this->enumClass = $enumClass;
    }

    /**
     * Transforms EnumInterface object to a raw enumerated value.
     *
     * @param Enum|null $value Enum instance
     *
     * @throws TransformationFailedException When the transformation fails
     *
     * @return int|string|null Value of EnumInterface
     */
    public function transform($value)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof $this->enumClass) {
            throw new TransformationFailedException(
                sprintf(
                    'Expected instance of "%s". Got "%s".',
                    $this->enumClass,
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }

        return $value->getValue();
    }

    /**
     * Transforms a raw enumerated value to an enumeration instance.
     *
     * @param int|string|null $value Value accepted by Enum
     *
     * @throws TransformationFailedException When the transformation fails
     *
     * @return Enum|null A single EnumInterface instance or null
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            if (method_exists($this->enumClass, 'from')) {
                return $this->enumClass::from($value);
            }

            return new $this->enumClass($value);
        } catch (UnexpectedValueException $exception) {
            throw new TransformationFailedException(
                sprintf("Value '%s' is not part of the enum '%s'", $value, $this->enumClass),
                $exception->getCode(),
                $exception
            );
        }
    }
}
