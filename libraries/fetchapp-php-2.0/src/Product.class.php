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


class Product
{

    /**
     * @var $ProductID int
     */
    private $ProductID;
    
    /**
     * @var $SKU string
     */
    private $SKU;
    
    /**
     * @var $Name string
     */
   	private $Name;
   	
   	/**
     * @var $Price float
     */
   	private $Price;
   	
   	/**
     * @var $Currency \FetchApp\API\Currency
     */
   	private $Currency;
   	
   	
   	/**
     * @var $OrderCount int
     */
   	private $OrderCount;
   	
   	/**
     * @var $DownloadCount int
     */
   	private $DownloadCount;
   	
   	/**
     * @var $PaypalAddToCartLink string
     */
   	private $PaypalAddToCartLink;
   	
   	/**
     * @var $PaypalBuyNowLink string
     */
   	private $PaypalBuyNowLink;
   	
   	/**
     * @var $PaypalViewCartLink string
     */
   	private $PaypalViewCartLink;

	/**
     * @var $CreationDate \DateTime
     */
    private $CreationDate;
    
    /**
     * @var $FilesUri string
     */
   	private $FilesUri;
   	
   	/**
     * @var $DownloadsUri string
     */
   	private $DownloadsUri;
   	
   	/**
     * @var $files array
     */
   	private $files;
   	
	
    function __construct()
    {
		$this->files = array();
    }
    
    /**
     * @return int
     */
    public function getProductID(){ return $this->ProductID; }
    
    /**
     * @param int $ProductID
     */
    public function setProductID($ProductID){ $this->ProductID = $ProductID; }
	
	/**
     * @return string
     */
    public function getSKU(){ return $this->SKU; }
   
   	/**
     * @param string $SKU
     */
    public function setSKU($SKU){ $this->SKU = $SKU; }
	
	/**
     * @return string
     */
    public function getName(){ return $this->Name; }
    
    /**
     * @param string $Name
     */
    public function setName($Name){ $this->Name = $Name; }
	
	/**
     * @return float
     */
    public function getPrice(){ return $this->Price; }
    
    /**
     * @param float $Price
     */
    public function setPrice($Price){ $this->Price = $Price; }
	
	/**
     * @return int
     */
    public function getOrderCount(){ return $this->OrderCount; }
    
    /**
     * @param int $OrderCount
     */
    public function setOrderCount($OrderCount){ $this->OrderCount = $OrderCount; }
	
	/**
     * @return int
     */
    public function getDownloadCount(){ return $this->DownloadCount; }
    
    /**
     * @param int $DownloadCount
     */
    public function setDownloadCount($DownloadCount){ $this->DownloadCount = $DownloadCount; }
	
	/**
     * @return string
     */
    public function getPaypalAddToCartLink(){ return $this->PaypalAddToCartLink; }
    
    /**
     * @param string $PaypalAddToCartLink
     */
    public function setPaypalAddToCartLink($PaypalAddToCartLink){ $this->PaypalAddToCartLink = $PaypalAddToCartLink; }
	
	/**
     * @return string
     */
    public function getPaypalBuyNowLink(){ return $this->PaypalBuyNowLink; }
   	
   	/**
     * @param string $PaypalBuyNowLink
     */
    public function setPaypalBuyNowLink($PaypalBuyNowLink){ $this->PaypalBuyNowLink = $PaypalBuyNowLink; }
	
	/**
     * @return string
     */
    public function getPaypalViewCartLink(){ return $this->PaypalViewCartLink; }
    
    /**
     * @param string $PaypalViewCartLink
     */
    public function setPaypalViewCartLink($PaypalViewCartLink){ $this->PaypalViewCartLink = $PaypalViewCartLink; }
	
    /**
     * @return string
     */
    public function getFilesUri(){ return $this->FilesUri; }
    
    /**
     * @param string $FilesUri
     */
    public function setFilesUri($FilesUri){ $this->FilesUri = $FilesUri; }

	/**
     * @return string
     */
    public function getDownloadsUri(){ return $this->DownloadsUri; }
    
    /**
     * @param string $DownloadsUri
     */
    public function setDownloadsUri($DownloadsUri){ $this->DownloadsUri = $DownloadsUri; }

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
     * @param array $files
     * @return mixed
     */
    public function create(array $files)
    {
        APIWrapper::verifyReadiness();
        $this->files = $files;

        $url = "https://app.fetchapp.com/api/v2/products/create";
        $data = $this->toXML();

        $response = APIWrapper::makeRequest($url, "POST", $data);

        if (isset($response->id)) {
            $this->setProductID($response->id);
            $this->setSKU($response->sku);
            $this->setName($response->name);
            $this->setPrice($response->price);
            $this->setOrderCount($response->order_count);
            $this->setDownloadCount($response->download_count);
            $this->setPaypalAddToCartLink($response->paypal_add_to_cart_link);
            $this->setPaypalBuyNowLink($response->paypal_buy_now_link);
            $this->setPaypalViewCartLink($response->paypal_view_cart_link);
            $this->setCreationDate(new \DateTime($response->created_at));
            $this->setFilesUri($response->files_uri);
            $this->setDownloadsUri($response->downloads_uri);
            return true;
        } else {
            // It failed, let's return the error
            return $response[0];
        }
    }

    /**
     * @param array $files
     * @return mixed
     */
    public function update(array $files)
    {
        APIWrapper::verifyReadiness();
        $this->files = $files;

        $url = "https://app.fetchapp.com/api/v2/products/" . $this->ProductID . "/update";
        $data = $this->toXML();

        $response = APIWrapper::makeRequest($url, "PUT", $data);
        if (isset($response->id)) {
            $this->setProductID($response->id);
            $this->setSKU($response->sku);
            $this->setName($response->name);
            $this->setPrice($response->price);
            $this->setOrderCount($response->order_count);
            $this->setDownloadCount($response->download_count);
            $this->setPaypalAddToCartLink($response->paypal_add_to_cart_link);
            $this->setPaypalBuyNowLink($response->paypal_buy_now_link);
            $this->setPaypalViewCartLink($response->paypal_view_cart_link);
            $this->setCreationDate(new \DateTime($response->created_at));
            $this->setFilesUri($response->files_uri);
            $this->setDownloadsUri($response->downloads_uri);
            return true;
        } else {
            // It failed, let's return the error
            return $response[0];
        }
    }

	/**
     * @return mixed
     */
    public function delete()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/products/" . $this->ProductID . "/delete";
		$response = APIWrapper::makeRequest($requestURL, "DELETE");
		return $response;
    }

    /**
     * @return OrderDownload[] $downloads
     */
    public function getDownloads()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/products/" . $this->ProductID . "/downloads";
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
     * @return ProductStatistic[] $statistics
     */
    public function getStatistics()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/products/" . $this->ProductID . "/stats";
        $results = APIWrapper::makeRequest($requestURL, "GET");
        $stats = new ProductStatistic();
        $stats->setProductID((string)$results->id);
        $stats->setSKU((string)$results->sku);
        $stats->setDownloadCount((int)$results->download_count);
        $stats->setOrderCount((int)$results->order_count);
        $stats->setPrice((float)$results->price);
        $stats->setCurrency(Currency::getValue((string)$results->currency));
        return $stats;
    }

    /**
     * @return FileDetail[] $downloads
     */
    public function getFiles()
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/products/" . $this->ProductID . "/files";
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
     * @return \SimpleXMLElement
     */
    public function toXML($sendEmailFlag = true)
    {
        $productXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . '<product></product>');
        $productXML->addChild("id", $this->ProductID);
        $productXML->addChild("sku", $this->SKU);
        $productXML->addChild("name", $this->Name);
        $priceElement = $productXML->addChild("price", $this->Price);
        $priceElement->addAttribute("type", "float");
        $productXML->addChild("currency", Currency::getName($this->Currency));

        /* Confirm these elements are accepted; not in API spec */
        /* ToDo: Add these as HREF */
        $productXML->addChild("paypal_add_to_cart_link", $this->PaypalAddToCartLink);
        $productXML->addChild("paypal_buy_now_link", $this->PaypalBuyNowLink);
        $productXML->addChild("paypal_view_cart_link", $this->PaypalViewCartLink);

        $productXML->addChild("files_uri", $this->FilesUri);
        $productXML->addChild("downloads_uri", $this->DownloadsUri);

        if(is_a($this->CreationDate, "DateTime")) {
            $creationDateElement = $productXML->addChild("created_at", $this->CreationDate->format(\DateTime::ISO8601));
            $creationDateElement->addAttribute("type", "datetime");
        }

        $filesElement = $productXML->addChild("files");
        $filesElement->addAttribute("type", "array");
        foreach ($this->files as $file) {
            $fileElm = $filesElement->addChild("file");
            // Check This
            $fileElm->addChild("id", $file->getFileID() );
        }

        return $productXML->asXML();
    }
}