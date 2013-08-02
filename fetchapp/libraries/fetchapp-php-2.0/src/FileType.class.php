<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:23 PM
 */

namespace FetchApp\API;
include_once "EnumEmulator.class.php";


class FileType extends EnumEmulator
{
    const Download = 0;
    const Link = 1;

    private static $className = "FetchApp\\API\\FileType";

    public static function getName($const)
    {
        return parent::getName($const, FileType::$className);
    }

    public static function getOptions()
    {
        return parent::getOptions(FileType::$className);
    }

    public static function getValue($name)
    {
        return parent::getValue($name, FileType::$className);
    }
}