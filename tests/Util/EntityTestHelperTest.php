<?php

namespace mmo\sf\tests\Util;

use InvalidArgumentException;
use mmo\sf\tests\data\EntityParentClass;
use mmo\sf\Util\EntityTestHelper;
use PHPUnit\Framework\TestCase;

class EntityTestHelperTest extends TestCase
{
    public function testSetPrivateEntityProperty(): void
    {
        $id = 123;
        $name = 'foo';
        $entity = $this->getEntity();

        EntityTestHelper::setPrivateProperty($entity, $id);
        EntityTestHelper::setPrivateProperty($entity, $name, 'name');

        $this->assertSame($id, $entity->getId());
        $this->assertSame($name, $entity->getName());
    }

    public function testThrowExceptionWhenPropertyNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $entity = $this->getEntity();
        EntityTestHelper::setPrivateProperty($entity, 'foo', 'foo');
    }

    private function getEntity(): object
    {
        return new class() extends EntityParentClass {
            private $name;

            public function getName()
            {
                return $this->name;
            }
        };
    }
}
