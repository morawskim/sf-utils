<?php

namespace mmo\sf\Form\DataTransformer;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Based on Symfony 5.x UuidToStringTransformer. Uses ramsey/uuid library.
 *
 * @link https://github.com/symfony/form/blob/5.x/Extension/Core/DataTransformer/UuidToStringTransformer.php
 */
class RamseyUuidToStringTransformer implements DataTransformerInterface
{
    /**
     * Transforms a Uuid object into a string.
     *
     * @param UuidInterface $value A Uuid object
     *
     * @return string|null A value as produced by Uid component
     *
     * @throws TransformationFailedException If the given value is not a Uuid object
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof UuidInterface) {
            throw new TransformationFailedException('Expected a Uuid.');
        }

        return (string) $value;
    }

    /**
     * Transforms a UUID string into a Uuid object.
     *
     * @param string $value A UUID string
     *
     * @return UuidInterface|null An instance of Uuid
     *
     * @throws TransformationFailedException If the given value is not a string,
     *                                       or could not be transformed
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        try {
            $uuid = Uuid::fromString($value);
        } catch (InvalidUuidStringException $e) {
            throw new TransformationFailedException(sprintf('The value "%s" is not a valid UUID.', $value), $e->getCode(), $e);
        }

        return $uuid;
    }
}
