<?php

namespace mmo\sf\Util;

use ReflectionClass;
use ReflectionException;

class EntityTestHelper
{
    /**
     * @param object $entity
     * @param $value
     * @param string $propertyName
     *
     * @throws ReflectionException
     */
    public static function setPrivateProperty(object $entity, $value, string $propertyName = 'id'): void
    {
        $class = new ReflectionClass($entity);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($entity, $value);
    }
}
