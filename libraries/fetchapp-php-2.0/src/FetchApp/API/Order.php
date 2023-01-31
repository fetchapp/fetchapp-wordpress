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


class Order
{

    /**
     * @var $OrderID int
     */
    private $OrderID;
    /**
     * @var $VendorID int
     */
    private $VendorID;
    /**
     * @var $FirstName String
     */
    private $FirstName;
    /**
     * @var $LastName String
     */
    private $LastName;
    /**
     * @var $EmailAddress String
     */
    private $EmailAddress;
    /**
     * @var $Total float
     */
    private $Total;
    /**
     * @var $Currency int
     */
    private $Currency;
    /**
     * @var $Status int
     */
    private $Status;
    /**
     * @var $ProductCount int
     */
    private $ProductCount;
    /**
     * @var $DownloadCount int
     */
    private $DownloadCount;
    /**
     * @var $ExpirationDate \DateTime
     */
    private $ExpirationDate;
    /**
     * @var $DownloadLimit int
     */
    private $DownloadLimit;
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
    /**
     * @var $Link string
     */
    private $Link;
    /**
     * @var $items OrderItem[]
     */
    private $items;

    // PRC 10.2020
    private $send_email;

    function __construct()
    {
        $this->items = array();
    }

    /**
     * @param \DateTime $CreationDate
     */
    public function setCreationDate($CreationDate)
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
     * @param int $DownloadLimit
     */
    public function setDownloadLimit($DownloadLimit)
    {
        $this->DownloadLimit = $DownloadLimit;
    }

    /**
     * @return int
     */
    public function getDownloadLimit()
    {
        return $this->DownloadLimit;
    }

    /**
     * @param String $EmailAddress
     */
    public function setEmailAddress($EmailAddress)
    {
        $this->EmailAddress = $EmailAddress;
    }

    /**
     * @return String
     */
    public function getEmailAddress()
    {
        return $this->EmailAddress;
    }

    /**
     * @param \DateTime $ExpirationDate
     */
    public function setExpirationDate($ExpirationDate)
    {
        $this->ExpirationDate = $ExpirationDate;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->ExpirationDate;
    }

    /**
     * @param String $FirstName
     */
    public function setFirstName($FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return String
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param String $LastName
     */
    public function setLastName($LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return String
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param int $OrderID
     */
    public function setOrderID($OrderID)
    {
        $this->OrderID = $OrderID;
    }

    /**
     * @return int
     */
    public function getOrderID()
    {
        return (int)$this->OrderID;
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
     * @param int $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param float $Total
     */
    public function setTotal($Total)
    {
        $this->Total = $Total;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->Total;
    }

    /**
     * @param int $VendorID
     */
    public function setVendorID($VendorID)
    {
        $this->VendorID = $VendorID;
    }

    /**
     * @return int
     */
    public function getVendorID()
    {
        return $this->VendorID;
    }

    /**
     * @param string $Link
     */
    public function setLink($Link)
    {
        $this->Link = $Link;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->Link;
    }

    /**
     * @param array $items
     * @param bool $sendEmail
     * @return bool
     */
    public function create(array $items, $sendEmail = true)
    {
        APIWrapper::verifyReadiness();
        $this->items = $items;

        $url = "/orders";

        $data = $this->toPostData($sendEmail);

        $response = APIWrapper::makeRequest($url, "POST", $data);

        if (isset($response->order->id)) {
            // It worked, let's fill in the rest of the data
            $this->loadFromJSON($response->order);
            return true;
        } else {
            // It failed, let's return the error
            return $response;
        }
    }

    /**
     * @param array $items
     * @param bool $sendEmail
     * @return bool
     */
    public function update(array $items, $sendEmail = true)
    {
        APIWrapper::verifyReadiness();
        $this->items = $items;
        $url = "/orders/" . $this->OrderID;
        $data = $this->toPostData($sendEmail);
        $response = APIWrapper::makeRequest($url, "PUT", $data);

        if (isset($response->order->id)) {
            // It worked, let's fill in the rest of the data
            $this->loadFromJSON($response->order);
            return true;
        } else {
            // It failed, let's return the error
            return $response;
        }
    }

    /**
     * @return mixed
     */
    public function expire()
    {
        APIWrapper::verifyReadiness();

        $requestURL = "/orders/" . $this->OrderID . "/expire";
        $response = APIWrapper::makeRequest($requestURL, "POST");

		return $response;
    }

    /**
     * @return mixed
     */
    public function reopen()
    {
        APIWrapper::verifyReadiness();

        $requestURL = "/orders/" . $this->OrderID . "/reopen";
        $response = APIWrapper::makeRequest($requestURL, "POST");

        return $response;
    }

    /**
     * @return mixed
     */
    public function resend()
    {
        APIWrapper::verifyReadiness();

        $requestURL = "/orders/" . $this->OrderID . "/resend";
        $response = APIWrapper::makeRequest($requestURL, "POST");

        return $response;
    }
	
	/**
     * @return mixed
     */
    public function delete()
    {
        APIWrapper::verifyReadiness();
        $url = "/orders/" . $this->OrderID;
        $response = APIWrapper::makeRequest($url, "DELETE");

		return $response;
    }
	
	/**
     * @return mixed
     */
    public function sendDownloadEmail($resetExpiration = true, \DateTime $expirationDate = null, $downloadLimit = -1)
    {   
        $update_required = false;
        if($resetExpiration && $expirationDate):
            $this->setExpirationDate($expirationDate);
            $update_required = true;
        endif;

        if($downloadLimit !== -1):
            $this->setDownloadLimit((int)$downloadLimit);
            $update_required = true;
        endif;

        if($update_required):
            $items = $this->getItems();
            $this->update($items);
        endif;

        return $this->resend();
    }

    /**
     * @return OrderDownload[] $downloads
     */
    public function getDownloads()
    {
        APIWrapper::verifyReadiness();

        $requestURL = "/orders/" . $this->OrderID . "/downloads";
        $results = APIWrapper::makeRequest($requestURL, "GET");

        $downloads = array();

        foreach ($results->downloads as $json_download) {
            $download = new OrderDownload();
            $download->loadFromJSON($json_download);
            $downloads[] = $download;
        }
        return $downloads;
    }

    /**
     * @return OrderStatistic[] $statistics
     */
    public function getStatistics()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/stats";
        $results = APIWrapper::makeRequest($requestURL, "GET");
        $stats = new OrderStatistic();
        $stats->setOrderID((string)$results->id);
        $stats->setVendorID((string)$results->vendor_id);
        $stats->setDownloadCount((int)$results->download_count);
        $stats->setProductCount((int)$results->product_count);
        $stats->setOrderTotal((float)$results->total);
        $stats->setCurrency(Currency::getValue((string)$results->currency));
        return $stats;
    }

    /**
     * @return OrderItem[] $items
     */
    public function getItems()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "/orders/" . $this->OrderID;
        $results = APIWrapper::makeRequest($requestURL, "GET");
        $items = array();

        foreach ($results->order->order_items as $item) :
            $i = new OrderItem();
            $i->loadFromJSON($item);
            $items[] = $i;
        endforeach;
        return $items;
    }
	
	/**
     * @return \SimpleXMLElement
     */
    public function toXML($sendEmailFlag = true)
    {
        $orderXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . '<order></order>', LIBXML_NOEMPTYTAG);
        $orderXML->addChild("id", $this->OrderID);
        $orderXML->addChild("vendor_id", $this->VendorID);
        $orderXML->addChild("first_name", $this->FirstName);
        $orderXML->addChild("last_name", $this->LastName);
        $orderXML->addChild("email", $this->EmailAddress);
        $orderXML->addChild("currency", Currency::getName($this->Currency));
        $c1 = $orderXML->addChild("custom_1", $this->Custom1);
        if (empty($this->Custom1)) {
            $c1->addAttribute("nil", "true");
        }
        $c2 = $orderXML->addChild("custom_2", $this->Custom2);
        if (empty($this->Custom2)) {
            $c2->addAttribute("nil", "true");
        }
        $c3 = $orderXML->addChild("custom_3", $this->Custom3);
        if (empty($this->Custom3)) {
            $c3->addAttribute("nil", "true");
        }
        if(is_a($this->ExpirationDate, "DateTime")) {
            $expirationDateElement = $orderXML->addChild("expiration_date", $this->ExpirationDate->format(\DateTime::ISO8601));
            $expirationDateElement->addAttribute("type", "datetime");
        }

        if($this->DownloadLimit > 0) {
            $downloadLimitElement = $orderXML->addChild("download_limit", $this->DownloadLimit);
            $downloadLimitElement->addAttribute("type", "integer");
        }
        
        $orderXML->addChild("send_email", ($sendEmailFlag ? "true" : "false"));
        $orderItemsElement = $orderXML->addChild("order_items");
        $orderItemsElement->addAttribute("type", "array");
        foreach ($this->items as $item) {
            $orderItem = $orderItemsElement->addChild("order_item");
            $orderItem->addChild("sku", $item->getSKU());
            $downloadsRemainingElement = $orderItem->addChild("downloads_remaining", $item->getDownloadsRemaining());
            $downloadsRemainingElement->addAttribute("type", "integer");
            $priceElement = $orderItem->addChild("price", $item->getPrice());
            $priceElement->addAttribute("type", "float");
        }


        return $orderXML->asXML();
    }

    public function toPostData($sendEmailFlag = true){
        $json_object = new \stdClass();
        $json_object->id = $this->OrderID;
        $json_object->vendor_id = $this->VendorID;

        $json_object->first_name = $this->FirstName;
        $json_object->last_name = $this->LastName;

        $json_object->email = $this->EmailAddress;
        $json_object->currency = Currency::getName($this->Currency);

        $json_object->custom_1 = $this->getCustom1();
        $json_object->custom_2 = $this->getCustom2();
        $json_object->custom_3 = $this->getCustom3();

        if(is_a($this->ExpirationDate, "DateTime")) :
            $json_object->expiration_date = $this->ExpirationDate->format(\DateTime::ISO8601);
        endif;

        if($this->DownloadLimit > 0) :
            $json_object->download_limit = $this->DownloadLimit;
        endif;

        $json_object->send_email = ($sendEmailFlag ? "true" : "false");

        $json_object->order_items = [];

        foreach ($this->items as $item) :
            $order_item = $item->toPostData();
            $json_object->order_items[] = $order_item;
        endforeach;

        $output = array('order' => $json_object);
        return $output;
    }

    public function loadFromJSON($json){
        if (is_object($json) ) :
            $this->setOrderID($json->id);
            $this->setVendorID($json->vendor_id);
            $this->setFirstName($json->first_name);
            $this->setLastName($json->last_name);
            $this->setEmailAddress($json->email);
            $this->setTotal($json->total);
            $this->setCurrency(Currency::getValue($json->currency));
            $this->setStatus(OrderStatus::getValue($json->status));
            $this->setProductCount($json->product_count);
            $this->setDownloadCount($json->download_count);

            if($json->expiration_date):
                $this->setExpirationDate(new \DateTime($json->expiration_date));
            endif;

            $this->setDownloadLimit($json->download_limit);
            $this->setCustom1($json->custom_1);
            $this->setCustom2($json->custom_2);
            $this->setCustom3($json->custom_3);
            $this->setCreationDate(new \DateTime($json->created_at));
            $this->setLink($json->download_url);

            // PRC 10.2020 - Added this behavior
            if(isset($json->order_items) ):
                $items = array();
                foreach($json->order_items as $json_item):
                    $order_item = new OrderItem();
                    $order_item->loadFromJSON($json_item);
                    $items[] = $order_item;
                endforeach;
                $this->items = $items;
            endif;
        endif;
        
        return true;
    }
}