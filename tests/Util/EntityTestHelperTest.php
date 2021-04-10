<?php

namespace mmo\sf\tests\Util;

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

    private function getEntity(): object
    {
        return new class() {
            private $id;
            private $name;

            public function getId()
            {
                return $this->id;
            }

            public function getName()
            {
                return $this->name;
            }
        };
    }
}
