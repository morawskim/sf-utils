<?php

namespace mmo\sf\Util;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class EntityTestHelper
{
    /**
     * @param object $entity
     * @param $value
     * @param string $propertyName
     *
     * @throws InvalidArgumentException when property not exists
     */
    public static function setPrivateProperty(object $entity, $value, string $propertyName = 'id'): void
    {
        $class = new ReflectionClass($entity);
        $property = self::getProperty($class, $propertyName);
        $property->setAccessible(true);
        $property->setValue($entity, $value);
    }

    /**
     * @param ReflectionClass $class
     * @param string $propertyName
     *
     * @throws InvalidArgumentException when property not exists
     *
     * @return ReflectionProperty
     */
    private static function getProperty(ReflectionClass $class, string $propertyName): ReflectionProperty
    {
        if ($class->hasProperty($propertyName)) {
            return $class->getProperty($propertyName);
        }

        $rc = $class;

        while ($rc = $rc->getParentClass()) {
            if ($rc->hasProperty($propertyName)) {
                return $rc->getProperty($propertyName);
            }
        }

        throw new InvalidArgumentException(sprintf('Property "%s" not exists', $propertyName));
    }
}
