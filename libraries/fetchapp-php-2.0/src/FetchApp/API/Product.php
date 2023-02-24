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

    /**
     * @var $item_urls array
     */
    private $item_urls;
   	
	
    function __construct()
    {
		$this->files = array();
        $this->item_urls = array();
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
    public function getSKU(){ return (string)$this->SKU; }
   
   	/**
     * @param string $SKU
     */
    public function setSKU($SKU){ $this->SKU = $SKU; }
	
	/**
     * @return string
     */
    public function getName(){ return (string)$this->Name; }
    
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
     * @param array $item_urls
     * @return mixed
     */
    public function create(array $files, array $item_urls = array() )
    {
        APIWrapper::verifyReadiness();
        $this->files = $files;
        $this->item_urls = $item_urls;

        $url = "/products";
        $data = $this->toPostData();

        $response = APIWrapper::makeRequest($url, "POST", $data);

        if (isset($response->product->id)) {
            $product = $response->product;
            $this->loadFromJSON($product);
            return true;
        } else {
            // It failed, let's return the error
            return $response;
        }
    }

    /**
     * @param array $files
     * @param array $item_urls
     * @return mixed
     */
    public function update(array $files, $item_urls = false )
    {
        APIWrapper::verifyReadiness();
        $this->files = $files;

        if($item_urls !== false):
            $this->item_urls = $item_urls;
        endif;

        $url = "/products/" . $this->ProductID; 
        $data = $this->toPostData();

        $response = APIWrapper::makeRequest($url, "PUT", $data);

        if (isset($response->product->id)) :
            $product = $response->product;
            $this->loadFromJSON($product);
            return true;
        else:
            // It failed, let's return the error
            return $response;
        endif;
    }

    /**
     * @param array $files
     * @param array $item_urls
     * @return mixed
     */
    public function updateBySku(array $files, $item_urls = false )
    {
        APIWrapper::verifyReadiness();
        $this->files = $files;

        if($item_urls !== false):
            $this->item_urls = $item_urls;
        endif;

        $url = "/skuproducts/" . $this->getSKU(); 
        $data = $this->toPostData();

        $response = APIWrapper::makeRequest($url, "PUT", $data);

        if (isset($response->product->id)) :
            $product = $response->product;
            $this->loadFromJSON($product);
            return true;
        else:
            // It failed, let's return the error
            return $response;
        endif;
    }

	/**
     * @return mixed
     */
    public function delete()
    {
        APIWrapper::verifyReadiness();
        $url = "/products/" . $this->ProductID;
        $response = APIWrapper::makeRequest($url, "DELETE");
        return $response;
    }

    /**
     * @return OrderDownload[] $downloads
     */
    public function getDownloads()
    {

        APIWrapper::verifyReadiness();

        $requestURL = "/products/" . $this->ProductID . "/downloads";
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

        $requestURL = "/products/" . $this->ProductID . "/files";

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

        if(! empty($this->item_urls) ):
            $itemUrlsElement = $productXML->addChild("item_urls");
            $itemUrlsElement->addAttribute("type", "array");
            foreach ($this->item_urls as $item_url) {
                if(isset($item_url['url'])):
                    $itemUrlsElm = $itemUrlsElement->addChild("item_url");
                    $itemUrlsElm->addChild("url", $item_url['url'] );
                    
                    if(isset($item_url['name'])):
                        $itemUrlsElm->addChild("name", $item_url['name'] );
                    endif;
                endif;
            }
        endif;

        return $productXML->asXML();
    }

    public function toPostData(){
        $json_object = new \stdClass();

        $json_object->id = $this->ProductID;
        $json_object->sku = $this->SKU;
        $json_object->name = $this->Name;
        $json_object->price = $this->Price;
        $json_object->currency = Currency::getName($this->Currency);

        // TODO: Check API 
        // $json_object->paypal_add_to_cart_link = $this->PaypalAddToCartLink;
        // $json_object->paypal_buy_now_link = $this->PaypalBuyNowLink;
        // $json_object->paypal_view_cart_link = $this->PaypalViewCartLink;
        // $json_object->files_uri = $this->FilesUri;
        // $json_object->downloads_uri = $this->DownloadsUri;

        if(is_a($this->CreationDate, "DateTime")) :
            $json_object->created_at = $this->CreationDate->format(\DateTime::ISO8601);
        endif;

        $json_object->files = [];
        foreach ($this->files as $file) :
            if($file->getFileID() ):
                $fileElm = new \stdClass();
                $fileElm->id = $file->getFileID();
                $json_object->files[] = $fileElm;
            endif;
        endforeach;

        $json_object->item_urls = [];
        foreach ($this->item_urls as $item_url) :
            if(is_object($item_url)):
                if(isset($item_url->url)):
                    $itemUrlsElm = new \stdClass();
                    $itemUrlsElm->url = $item_url->url;
                    
                    if(isset($item_url->name)):
                        $itemUrlsElm->name = $item_url->name;
                    endif;

                    $json_object->item_urls[] = $itemUrlsElm;
                endif;
            elseif(is_array($item_url) ):
                if(isset($item_url['url'])):
                    $itemUrlsElm = new \stdClass();
                    $itemUrlsElm->url = $item_url['url'];
                    
                    if(isset($item_url['name'])):
                        $itemUrlsElm->name = $item_url['name'];
                    endif;

                    $json_object->item_urls[] = $itemUrlsElm;
                endif;
            endif;
        endforeach;

        $output = array('product' => $json_object);
        return $output;
    }

    public function loadFromJSON($json){
        if (is_object($json) ) :
            $this->setProductID($json->id);
            $this->setSKU($json->sku);
            $this->setName($json->name);
            $this->setPrice($json->price);
            $this->setCurrency(Currency::getValue($json->currency));
            $this->setOrderCount($json->orders_count);
            $this->setDownloadCount($json->download_count);
            
            // TODO: NEED IN API
            // $this->setPaypalAddToCartLink($json->paypal_add_to_cart_link['href']);
            // $this->setPaypalBuyNowLink($json->paypal_buy_now_link['href']);
            // $this->setPaypalViewCartLink($json->paypal_view_cart_link['href']);

            $this->setCreationDate(new \DateTime($json->created_at));

            $this->item_urls = $json->item_urls;

            // TODO: NEED IN API
            // $this->setFilesUri($json->files_uri);
            // $this->setDownloadsUri($json->downloads_uri);
        endif;
        
        return true;
    }
}