<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:21 PM
 */
namespace FetchApp\API;
include_once "EnumEmulator.class.php";

class Currency extends EnumEmulator
{
    private static $className = "FetchApp\\API\\Currency";
    /**
     * Australian Dollar
     */
    const AUD = 0;
    /**
     * United States Dollar
     */
    const USD = 1;
    /**
     * Euro
     */
    const EUR = 2;
    /**
     * Pound Sterling
     */
    const GBP = 3;
    /**
     * Danish Krone
     */
    const DKK = 4;
    /**
     * Chinese Yuan
     */
    const CNY = 5;
    /**
     * Norwegian Krone
     */
    const NOK = 6;
    /**
     * New Zealand Dollar
     */
    const NZD = 7;
    /**
     * Russian Rouble
     */
    const RUB = 8;
    /**
     * East Caribbean Dollar
     */
    const XCD = 9;

    public static function getName($const)
    {
        return parent::getName($const, Currency::$className);
    }

    public static function getOptions()
    {
        return parent::getOptions(Currency::$className);
    }

    public static function getValue($name)
    {
        return parent::getValue($name, Currency::$className);
    }
}