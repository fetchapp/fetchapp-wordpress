<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:25 PM
 */

namespace FetchApp\API;


class OrderStatus extends EnumEmulator
{

    private static $className = "FetchApp\\API\\OrderStatus";

    const Open = 0;
    const Expired = 1;
    const All = 2;

    public static function getName($const)
    {
        return parent::getName($const, OrderStatus::$className);
    }

    public static function getOptions()
    {
        return parent::getOptions(OrderStatus::$className);
    }

    public static function getValue($name)
    {
        return parent::getValue($name, OrderStatus::$className);
    }
}