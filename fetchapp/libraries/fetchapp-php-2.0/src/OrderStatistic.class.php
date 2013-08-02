<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:25 PM
 */

namespace FetchApp\API;


class OrderStatistic
{
    /**
     * @var $OrderID String
     */
    private $OrderID;

    /**
     * @var $VendorID String
     */
    private $VendorID;

    /**
     * @var $DownloadCount int
     */
    private $DownloadCount;

    /**
     * @var $ProductCount int
     */
    private $ProductCount;

    /**
     * @var $OrderTotal float
     */
    private $OrderTotal;

    /**
     * @var int Currency
     */
    private $Currency;

    /**
     * @param int $Currency
     */
    public function setCurrency($Currency)
    {
        $this->Currency = $Currency;
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->Currency;
    }

    /**
     * @param int $DownloadCount
     */
    public function setDownloadCount($DownloadCount)
    {
        $this->DownloadCount = $DownloadCount;
    }

    /**
     * @return int
     */
    public function getDownloadCount()
    {
        return $this->DownloadCount;
    }

    /**
     * @param String $OrderID
     */
    public function setOrderID($OrderID)
    {
        $this->OrderID = $OrderID;
    }

    /**
     * @return String
     */
    public function getOrderID()
    {
        return $this->OrderID;
    }

    /**
     * @param float $OrderTotal
     */
    public function setOrderTotal($OrderTotal)
    {
        $this->OrderTotal = $OrderTotal;
    }

    /**
     * @return float
     */
    public function getOrderTotal()
    {
        return $this->OrderTotal;
    }

    /**
     * @param int $ProductCount
     */
    public function setProductCount($ProductCount)
    {
        $this->ProductCount = $ProductCount;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->ProductCount;
    }

    /**
     * @param String $VendorID
     */
    public function setVendorID($VendorID)
    {
        $this->VendorID = $VendorID;
    }

    /**
     * @return String
     */
    public function getVendorID()
    {
        return $this->VendorID;
    }


}