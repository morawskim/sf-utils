<?php

namespace mmo\sf\Serializer\Normalizer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MyCLabsEnumNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Enum) {
            throw new InvalidArgumentException(sprintf('The object must extends the "%s".', Enum::class));
        }

        return $object->getValue();
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Enum;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (null === $data) {
            return null;
        }

        try {
            if (method_exists($type, 'from')) {
                return $type::from($data);
            }

            return new $type($data);
        } catch (\UnexpectedValueException $exception) {
            throw new NotNormalizableValueException(
                sprintf("Value '%s' is not part of the enum '%s'", $data, $type),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_a($type, Enum::class, true);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
