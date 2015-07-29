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
	
	/**
     * @return mixed
     */
    public static function getName($const, $className='Currency')
    {
        return parent::getName($const, Currency::$className);
    }
	
	/**
     * @return array
     */
    public static function getOptions($className='Currency')
    {
        return parent::getOptions(Currency::$className);
    }
	
	/**
     * @return mixed
     */
    public static function getValue($name, $className='Currency')
    {
        return parent::getValue($name, Currency::$className);
    }
}