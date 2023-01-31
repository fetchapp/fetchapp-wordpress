<?php
/**
 * Created by JetBrains PhpStorm.
 * Updated by SublimeText 2.
 * Creator: Brendon Dugan <wishingforayer@gmail.com>
 * Last Updated: Patrick Conant <conantp@gmail.com>
 * User: Patrick Conant <conantp@gmail.com>
 * Date: 8/7/13
 * Time: 8:00 PM
 */

namespace FetchApp\API;


class EnumEmulator
{
	
	/**
     * @return mixed
     */
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
	
	/**
     * @return array
     */
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
	
	/**
     * @return mixed
     */
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