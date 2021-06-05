<?php

namespace mmo\sf\Serializer\Normalizer;

use Money\Currency;
use Money\Money;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MoneyNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Money) {
            throw new InvalidArgumentException(sprintf('The object must be instance of the "%s".', Money::class));
        }

        return $object->jsonSerialize();
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Money;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (null === $data) {
            return null;
        }

        if (!is_array($data)) {
            $type = is_object($data) ? get_class($data) : gettype($data);

            throw new NotNormalizableValueException(sprintf('Expected array, got "%s"', $type));
        }

        if (array_key_exists('amount', $data) && array_key_exists('currency', $data)) {
            return new Money($data['amount'], new Currency($data['currency']));
        }

        throw new NotNormalizableValueException(sprintf('Unexpected money format'));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_a($type, Money::class, true);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
