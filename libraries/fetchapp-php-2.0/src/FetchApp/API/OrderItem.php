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
     * @var $OrderItemID int
     */
    private $OrderItemID;

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
     * @param int $OrderItemID
     */
    public function setOrderItemID($OrderItemID)
    {
        $this->OrderItemID = $OrderItemID;
    }

    /**
     * @return int
     */
    public function getOrderItemID()
    {
        return $this->OrderItemID;
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
        // TODO: NOTE THIS USED TO BE $this->ItemID? 
        $requestURL = "/order_items/" . $this->OrderItemID . "/downloads";
        $downloads = array();
        $results = APIWrapper::makeRequest($requestURL, "GET");
        foreach ($results->downloads as $json_download) {
            $download = new OrderDownload();
            $download->loadFromJSON($json_download);
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

        // TODO: NOTE THIS USED TO BE $this->ItemID? 
        $requestURL = "/order_items/" . $this->OrderItemID . "/files";

        $files = array();
        $results = APIWrapper::makeRequest($requestURL, "GET");
        foreach ($results->files as $json_file) {
            $tempFile = new FileDetail();
            $tempFile->loadFromJSON($json_file);
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

    public function toPostData(){
        $order_item = new \stdClass();

        if($this->getOrderItemID() ):
            $order_item->id = $this->getOrderItemID();
        endif;

        if($this->getItemID() ):
            $order_item->item_id = $this->getItemID();
        endif;

        $order_item->item_sku = $this->getSKU();

        // TODO: Misssing - Order ID, Product Name, Downloads remaining        
        // $order_item->downloads_remaining = $this->getDownloadsRemaining();

        $order_item->download_count = $this->getDownloadCount();
        $order_item->price = $this->getPrice();

        $order_item->custom_1 = $this->getCustom1();
        $order_item->custom_2 = $this->getCustom1();
        $order_item->custom_3 = $this->getCustom1();

        return $order_item;
    }

    public function loadFromJSON($json){
        if (is_object($json) ) :
            $this->setOrderItemID((string)$json->id); // PRC - Added this for v3
            $this->setItemID((string)$json->item_id);
            $this->setSKU((string)$json->item_sku);

            // TODO: API
            // $i->setOrderID((string)$item->order_id);
            // TODO: API
            // $i->setProductName((string)$item->product_name);

            $this->setPrice((float)$json->price);
            $this->setDownloadCount((int)$json->download_count);       
            $this->setCustom1($json->custom_1);
            $this->setCustom2($json->custom_2);
            $this->setCustom3($json->custom_3);
            $this->setCreationDate(new \DateTime($json->created_at));

            // TODO: API
            // $i->setDownloadsRemaining(0); // We don't seem to be getting this back.
        endif;
        
        return true;
    }
}