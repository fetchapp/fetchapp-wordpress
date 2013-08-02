<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:25 PM
 */

namespace FetchApp\API;


class ProductStatistic
{
    /**
     * @var $ProductID String
     */
    private $ProductID;

    /**
     * @var $SKU String
     */
    private $SKU;

    /**
     * @var $DownloadCount int
     */
    private $DownloadCount;

    /**
     * @var $OrderCount int
     */
    private $OrderCount;

    /**
     * @var $Price float
     */
    private $Price;

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
     * @param String $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return String
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param float $price
     */
    public function setPrice($Price)
    {
        $this->Price = $Price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->Price;
    }

    /**
     * @param int $ProductCount
     */
    public function setOrderCount($OrderCount)
    {
        $this->OrderCount = $OrderCount;
    }

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return $this->OrderCount;
    }

    /**
     * @param String $SKU
     */
    public function setSKU($SKU)
    {
        $this->SKU = $SKU;
    }

    /**
     * @return String
     */
    public function getSKU()
    {
        return $this->SKU;
    }


}