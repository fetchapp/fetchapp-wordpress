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
        return $this->OrderID;
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
        $url = "https://app.fetchapp.com/api/v2/orders/create";
        $data = $this->toXML($sendEmail);
        $response = APIWrapper::makeRequest($url, "POST", $data);
        if (isset($response->id)) {
            // It worked, let's fill in the rest of the data
            $this->setTotal($response->total);
            $this->setStatus(OrderStatus::getValue($response->status));
            $this->setProductCount($response->product_count);
            $this->setLink($response->link["href"]);
            $this->setCreationDate(new \DateTime($response->created_at));
            return true;
        } else {
            // It failed, let's return the error
            return $response[0];
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
        $url = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/update";
        $data = $this->toXML($sendEmail);
        $response = APIWrapper::makeRequest($url, "PUT", $data);
        if (isset($response->id)) {
            // It worked, let's fill in the rest of the data
            $this->setTotal($response->total);
            $this->setStatus(OrderStatus::getValue($response->status));
            $this->setProductCount($response->product_count);
            $this->setLink($response->link["href"]);
            $this->setCreationDate(new \DateTime($response->created_at));
            return true;
        } else {
            // It failed, let's return the error
            return $response[0];
        }
    }

    /**
     * @return mixed
     */
    public function expire()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/expire";
        $response = APIWrapper::makeRequest($requestURL, "GET");
		return $response;
    }
	
	/**
     * @return mixed
     */
    public function delete()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/delete";
        $response = APIWrapper::makeRequest($requestURL, "DELETE");
		return $response;
    }
	
	/**
     * @return mixed
     */
    public function sendDownloadEmail($resetExpiration = true, \DateTime $expirationDate = null, $downloadLimit = -1)
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/send_email?";
        if ($resetExpiration === false) {
            $requestURL .= "reset_expiration=false";
        } else {
            if ($expirationDate != null) {
                $requestURL .= "expiration_date=" . $expirationDate->format(\DateTime::ISO8601);
            }
            if ($downloadLimit != -1) {
                $requestURL .= ($expirationDate != null) ? "&" : "";
                $requestURL .= "download_limit=" . $downloadLimit;
            }
        }
        $requestURL = rtrim($requestURL, '?');
        $response = APIWrapper::makeRequest($requestURL, "POST");
        return $response;
    }

    /**
     * @return OrderDownload[] $downloads
     */
    public function getDownloads()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/downloads";
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
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $this->OrderID . "/order_items";
        $results = APIWrapper::makeRequest($requestURL, "GET");
        $items = array();
        foreach ($results->order_item as $item) {
            $i = new OrderItem();
            $i->setItemID((string)$item->id);
            $i->setSKU((string)$item->sku);
            $i->setOrderID((string)$item->order_id);
            $i->setProductName((string)$item->product_name);
            $i->setPrice((float)$item->price);
            $i->setDownloadCount((int)$item->download_count);
            if (!isset($item->custom_1['nil'])) {
                $i->setCustom1($item->custom_1);
            } else {
                $i->setCustom1(null);
            }
            if (!isset($item->custom_2['nil'])) {
                $i->setCustom2($item->custom_2);
            } else {
                $i->setCustom2(null);
            }
            if (!isset($item->custom_3['nil'])) {
                $i->setCustom3($item->custom_3);
            } else {
                $i->setCustom3(null);
            }
            $i->setCreationDate(new \DateTime($item->created_at));
			// $i->setDownloadsRemaining(0); // We don't seem to be getting this back.

            $items[] = $i;
        }
        return $items;
    }
	
	/**
     * @return \SimpleXMLElement
     */
    public function toXML($sendEmailFlag = true)
    {
        $orderXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . '<order></order>');
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
        $downloadLimitElement = $orderXML->addChild("download_limit", $this->DownloadLimit);
        $downloadLimitElement->addAttribute("type", "integer");
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
}