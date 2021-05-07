<?php

namespace mmo\sf\Util;

use stdClass;

class ObjectHelper
{
    public static function arrayToObject(array $array): stdClass
    {
        $obj = new stdClass;

        foreach ($array as $k => $v) {
            if ($k !== '') {
                if (is_array($v)) {
                    $obj->{$k} = self::arrayToObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }
}