<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 3:42 PM
 */

namespace FetchApp\API;


class EnumEmulator
{

    public static function getName($const, $className)
    {
        $fakeEnumClass = new \ReflectionClass($className);
        $constants = $fakeEnumClass->getConstants();

        foreach ($constants as $name => $value) {
            if ($value == $const)
                return $name;
        }
        return false;
    }

    public static function getOptions($className)
    {
        $fakeEnumClass = new \ReflectionClass($className);
        $constants = $fakeEnumClass->getConstants();
        $options = array();
        foreach ($constants as $name => $value) {
            $options[] = $name;
        }
        return $options;
    }

    public static function getValue($name, $className)
    {
        $fakeEnumClass = new \ReflectionClass($className);
        $constants = $fakeEnumClass->getConstants();

        foreach ($constants as $n => $v) {
            if ($n == $name)
                return $v;
        }
        return false;
    }


}