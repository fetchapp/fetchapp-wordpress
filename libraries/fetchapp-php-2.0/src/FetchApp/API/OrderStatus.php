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


class OrderStatus extends EnumEmulator
{

    private static $className = "FetchApp\\API\\OrderStatus";

    const open = 0;
    const expired = 1;
    const all = 2;

    const Open = 0;
    const Expired = 1;
    const All = 2;
	
	/**
     * @return mixed
     */
    public static function getName($const, $className='OrderStatus')
    {
        return parent::getName($const, OrderStatus::$className);
    }
	
	/**
     * @return array
     */
    public static function getOptions($className='OrderStatus')
    {
        return parent::getOptions(OrderStatus::$className);
    }
	
	/**
     * @return mixed
     */
    public static function getValue($name, $className='OrderStatus')
    {
        return parent::getValue($name, OrderStatus::$className);
    }
}