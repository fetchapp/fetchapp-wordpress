<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:24 PM
 */

namespace FetchApp\API;


class OrderDownload
{

    /**
     * @var $DownloadID String
     */
    private $DownloadID;

    /**
     * @var $FileName String
     */
    private $FileName;

    /**
     * @var $SKU String
     */
    private $SKU;

    /**
     * @var $OrderID String
     */
    private $OrderID;

    /**
     * @var $OrderItemID String
     */
    private $OrderItemID;

    /**
     * @var $IPAddress String
     */
    private $IPAddress;

    /**
     * @var $DownloadedOn \DateTime
     */
    private $DownloadedOn;

    /**
     * @var $SizeInBytes int
     */
    private $SizeInBytes;

    function __construct()
    {
        $this->DownloadedOn = null;
    }

    /**
     * @param String $DownloadID
     */
    public function setDownloadID($DownloadID)
    {
        $this->DownloadID = $DownloadID;
    }

    /**
     * @return String
     */
    public function getDownloadID()
    {
        return $this->DownloadID;
    }

    /**
     * @param \DateTime $DownloadedOn
     */
    public function setDownloadedOn(\DateTime $DownloadedOn)
    {
        $this->DownloadedOn = $DownloadedOn;
    }

    /**
     * @return \DateTime
     */
    public function getDownloadedOn()
    {
        return $this->DownloadedOn;
    }

    /**
     * @param String $FileName
     */
    public function setFileName($FileName)
    {
        $this->FileName = $FileName;
    }

    /**
     * @return String
     */
    public function getFileName()
    {
        return $this->FileName;
    }

    /**
     * @param String $IPAddress
     */
    public function setIPAddress($IPAddress)
    {
        $this->IPAddress = $IPAddress;
    }

    /**
     * @return String
     */
    public function getIPAddress()
    {
        return $this->IPAddress;
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
     * @param String $OrderItemID
     */
    public function setOrderItemID($OrderItemID)
    {
        $this->OrderItemID = $OrderItemID;
    }

    /**
     * @return String
     */
    public function getOrderItemID()
    {
        return $this->OrderItemID;
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

    /**
     * @param int $SizeInBytes
     */
    public function setSizeInBytes($SizeInBytes)
    {
        $this->SizeInBytes = $SizeInBytes;
    }

    /**
     * @return int
     */
    public function getSizeInBytes()
    {
        return $this->SizeInBytes;
    }


}