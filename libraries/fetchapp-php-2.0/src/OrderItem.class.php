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


class OrderItem
{
    /**
     * @var $ItemID int
     */
    private $ItemID;

    /**
     * @var $SKU String
     */
    private $SKU;

    /**
     * @var $OrderID String
     */
    private $OrderID;

    /**
     * @var $ProductName String
     */
    private $ProductName;

    /**
     * @var $Price float
     */
    private $Price;

    /**
     * @var $DownloadCount int
     */
    private $DownloadCount;

    /**
     * @var $DownloadsRemaining int
     */
    private $DownloadsRemaining;

    /**
     * @var $Custom1 String
     */
    private $Custom1;

    /**
     * @var $Custom2 String
     */
    private $Custom2;

    /**
     * @var $Custom3 String
     */
    private $Custom3;

    /**
     * @var $CreationDate \DateTime
     */
    private $CreationDate;

    function __construct()
    {
        $this->CreationDate = null;
    }

    /**
     * @param \DateTime $CreationDate
     */
    public function setCreationDate(\DateTime $CreationDate)
    {
        $this->CreationDate = $CreationDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->CreationDate;
    }

    /**
     * @param String $Custom1
     */
    public function setCustom1($Custom1)
    {
        $this->Custom1 = $Custom1;
    }

    /**
     * @return String
     */
    public function getCustom1()
    {
        return $this->Custom1;
    }

    /**
     * @param String $Custom2
     */
    public function setCustom2($Custom2)
    {
        $this->Custom2 = $Custom2;
    }

    /**
     * @return String
     */
    public function getCustom2()
    {
        return $this->Custom2;
    }

    /**
     * @param String $Custom3
     */
    public function setCustom3($Custom3)
    {
        $this->Custom3 = $Custom3;
    }

    /**
     * @return String
     */
    public function getCustom3()
    {
        return $this->Custom3;
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
     * @param int $DownloadsRemaining
     */
    public function setDownloadsRemaining($DownloadsRemaining)
    {
        $this->DownloadsRemaining = $DownloadsRemaining;
    }

    /**
     * @return int
     */
    public function getDownloadsRemaining()
    {
        return $this->DownloadsRemaining;
    }

    /**
     * @param int $ItemID
     */
    public function setItemID($ItemID)
    {
        $this->ItemID = $ItemID;
    }

    /**
     * @return int
     */
    public function getItemID()
    {
        return $this->ItemID;
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
     * @param float $Price
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
     * @param String $ProductName
     */
    public function setProductName($ProductName)
    {
        $this->ProductName = $ProductName;
    }

    /**
     * @return String
     */
    public function getProductName()
    {
        return $this->ProductName;
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
     * @return OrderDownload[] $downloads
     */
    public function getDownloads()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/order_items/" . $this->ItemID . "/downloads";
        $downloads = array();
        $results = APIWrapper::makeRequest($requestURL, "GET");
        foreach ($results->download as $d) {
            $download = new OrderDownload();
            $download->setDownloadID((string)$d->id);
            $download->setFileName((string)$d->filename);
            $download->setSKU((string)$d->product_sku);
            $download->setOrderID((string)$d->order_id);
            $download->setOrderItemID((string)$d->order_item_id);
            $download->setIPAddress((string)$d->ip_address);
            $download->setDownloadedOn(new \DateTime($d->downloaded_at));
            $download->setSizeInBytes((int)$d->size_bytes);

            $downloads[] = $download;
        }
        return $downloads;
    }

    /**
     * @return FileDetail[] $downloads
     */
    public function getFiles()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/order_items/" . $this->ItemID . "/files";
        $files = array();
        $results = APIWrapper::makeRequest($requestURL, "GET");
        foreach ($results->file as $file) {
            $tempFile = new FileDetail();

            $tempFile->setFileID($file->id);
            $tempFile->setFileName($file->filename);
            $tempFile->setSizeInBytes($file->size_bytes);
            $tempFile->setContentType($file->content_type);
            $tempFile->setPermalink($file->permalink);
            $tempFile->setURL($file->url);

            $files[] = $tempFile;
        }
        return $files;
    }
    
    /**
     * @return mixed
     */
    public function expire()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/order_items/" . $this->ItemID . "/expire";
        $response = APIWrapper::makeRequest($requestURL, "GET");
        return $response;
    }


}