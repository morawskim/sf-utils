<?php

namespace mmo\sf\tests\Util;

use mmo\sf\Util\ObjectHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

class ObjectHelperTest extends TestCase
{
    /**
     * @dataProvider providerArrayToObject
     *
     * @param array $array
     * @param stdClass $expectedObject
     */
    public function testArrayToObject(array $array, stdClass $expectedObject): void
    {
        $this->assertEquals($expectedObject, ObjectHelper::arrayToObject($array));
    }

    public function providerArrayToObject(): iterable
    {
        yield [[], new stdClass()];
        yield [['' => 'bar'], new stdClass()];
        yield [[null => 'bar'], new stdClass()];
        yield [[0.0 => 'bar'], (object) [0 => 'bar']];
        yield [[0 => 'bar'], (object) [0 => 'bar']];
        yield [[20 => 'bar'], (object) [20 => 'bar']];
        yield [['0' => 'bar'], (object) ['0' => 'bar']];

        $object = new stdClass();
        $object->foo = 'bar';
        yield [['foo' => 'bar'], $object];

        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = new stdClass();
        $object->baz->foo = 'bar';
        yield [['foo' => 'bar', 'baz' => ['foo' => 'bar']], $object];
    }
}
