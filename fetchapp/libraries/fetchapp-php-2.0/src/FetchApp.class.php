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


class FetchApp
{

    /**
     * @var $AuthenticationKey String
     */
    private $AuthenticationKey;

    /**
     * @var $AuthenticationToken String
     */
    private $AuthenticationToken;

    function __construct()
    {
    }

    /**
     * @param String $AuthenticationKey
     */
    public function setAuthenticationKey($AuthenticationKey)
    {
        $this->AuthenticationKey = $AuthenticationKey;
        APIWrapper::setAuthenticationKey($AuthenticationKey);
    }

    /**
     * @return String
     */
    public function getAuthenticationKey()
    {
        return $this->AuthenticationKey;
    }

    /**
     * @param String $AuthenticationToken
     */
    public function setAuthenticationToken($AuthenticationToken)
    {
        $this->AuthenticationToken = $AuthenticationToken;
        APIWrapper::setAuthenticationToken($AuthenticationToken);
    }

    /**
     * @return String
     */
    public function getAuthenticationToken()
    {
        return $this->AuthenticationToken;
    }

    /**
     * @return AccountDetail
     */
    public function getAccountDetails()
    {
        APIWrapper::verifyReadiness();
        $detail = new AccountDetail();
        $results = APIWrapper::makeRequest("https://app.fetchapp.com/api/v2/account", "GET");
        if (is_a($results, "SimpleXMLElement")) {
            $detail->setAccountID($results->id);
            $detail->setAccountName($results->name);
            $detail->setEmailAddress($results->email);
            $detail->setURL($results->url);
            $detail->setBillingEmail($results->billing_email);
            if (!isset($results->order_expiration_in_hours['nil'])) {
                $detail->setOrderExpirationInHours($results->order_expiration_in_hours);
            } else {
                $detail->setOrderExpirationInHours(-1);
            }
            $detail->setItemDownloadLimit($results->download_limit_per_item);
            $detail->setCurrency(Currency::getValue($results->currency));
            $detail->setCreationDate(new \DateTime($results->created_at));
            $detail->setAPIKey($results->api_key);
            $detail->setAPIToken($results->api_token);
        }
        return $detail;
    }

    /**
     * @param int $status
     * @param $itemsPerPage
     * @param $pageNumber
     * @return Order[]
     */
    public function getOrders($status = OrderStatus::All, $itemsPerPage = -1, $pageNumber = -1)
    {
        APIWrapper::verifyReadiness();
        $orders = array();
        $requestURL = "https://app.fetchapp.com/api/v2/orders.xml?";
        if ($status != OrderStatus::All) {
            $requestURL .= "status=" . strtolower(OrderStatus::getName($status));
        }
        if ($itemsPerPage != -1) {
            $requestURL .= ($status != OrderStatus::All) ? "&" : "";
            $requestURL .= "per_page=" . $itemsPerPage;
        }
        if ($pageNumber != -1) {
            $requestURL .= ($status != OrderStatus::All || $itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        }
        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($results, "SimpleXMLElement")) {
            foreach ($results->order as $order) {
                $tempOrder = new Order();
                $tempOrder->setOrderID($order->id);
                $tempOrder->setVendorID($order->vendor_id);
                $tempOrder->setFirstName($order->first_name);
                $tempOrder->setLastName($order->last_name);
                $tempOrder->setEmailAddress($order->email);
                $tempOrder->setTotal($order->total);
                $tempOrder->setCurrency(Currency::getValue($order->currency));
                $tempOrder->setStatus(OrderStatus::getValue($order->status));
                $tempOrder->setProductCount($order->product_count);
                $tempOrder->setDownloadCount($order->download_count);
                $tempOrder->setExpirationDate(new \DateTime($order->expiration_date));
                $tempOrder->setDownloadLimit($order->download_limit);
                if (!isset($order->custom1['nil'])) {
                    $tempOrder->setCustom1($order->custom1);
                } else {
                    $tempOrder->setCustom1(null);
                }
                if (!isset($order->custom2['nil'])) {
                    $tempOrder->setCustom2($order->custom2);
                } else {
                    $tempOrder->setCustom2(null);
                }
                if (!isset($order->custom3['nil'])) {
                    $tempOrder->setCustom3($order->custom3);
                } else {
                    $tempOrder->setCustom3(null);
                }
                $tempOrder->setCreationDate(new \DateTime($order->created_at));
                $tempOrder->setLink($order->link['href']);
                $orders[] = $tempOrder;
            }

        }
        return $orders;
    }

    /**
     * @param $orderID
     * @return Order
     */
    public function getOrder($orderID)
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/orders/" . $orderID;
        $results = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($results, "SimpleXMLElement")) {
            $tempOrder = new Order();
            $tempOrder->setOrderID($results->id);
            $tempOrder->setVendorID($results->vendor_id);
            $tempOrder->setFirstName($results->first_name);
            $tempOrder->setLastName($results->last_name);
            $tempOrder->setEmailAddress($results->email);
            $tempOrder->setTotal($results->total);
            $tempOrder->setCurrency(Currency::getValue($results->currency));
            $tempOrder->setStatus(OrderStatus::getValue($results->status));
            $tempOrder->setProductCount($results->product_count);
            $tempOrder->setDownloadCount($results->download_count);
            $tempOrder->setExpirationDate(new \DateTime($results->expiration_date));
            $tempOrder->setDownloadLimit($results->download_limit);
            if (!isset($results->custom1['nil'])) {
                $tempOrder->setCustom1($results->custom1);
            } else {
                $tempOrder->setCustom1(null);
            }
            if (!isset($results->custom2['nil'])) {
                $tempOrder->setCustom2($results->custom2);
            } else {
                $tempOrder->setCustom2(null);
            }
            if (!isset($results->custom3['nil'])) {
                $tempOrder->setCustom3($results->custom3);
            } else {
                $tempOrder->setCustom3(null);
            }
            $tempOrder->setCreationDate(new \DateTime($results->created_at));
            $tempOrder->setLink($results->link['href']);
        }
        return $tempOrder;
    }
    
    /**
     * @param $itemsPerPage
     * @param $pageNumber
     * @return Product[]
     */
    public function getProducts($itemsPerPage = -1, $pageNumber = -1)
    {
        APIWrapper::verifyReadiness();
        $products = array();
        $requestURL = "https://app.fetchapp.com/api/v2/products.xml?";

        if ($itemsPerPage != -1) {
            $requestURL .= "per_page=" . $itemsPerPage;
        }
        
        if ($pageNumber != -1) {
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        }
        
        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($results, "SimpleXMLElement")) {
            foreach ($results->product as $product) {
                $tempProduct = new Product();
                $tempProduct->setProductID($product->id);
                $tempProduct->setSKU($product->sku);
                $tempProduct->setName($product->name);
                $tempProduct->setPrice($product->price);
                $tempProduct->setCurrency(Currency::getValue($product->currency));
                $tempProduct->setOrderCount($product->order_count);
                $tempProduct->setDownloadCount($product->download_count);
                $tempProduct->setPaypalAddToCartLink($product->paypal_add_to_cart_link['href']);
                $tempProduct->setPaypalBuyNowLink($product->paypal_buy_now_link['href']);
                $tempProduct->setPaypalViewCartLink($product->paypal_view_cart_link['href']);
                $tempProduct->setCreationDate(new \DateTime($product->created_at));
                $tempProduct->setFilesUri($product->files_uri);
                $tempProduct->setDownloadsUri($product->downloads_uri);

                $products[] = $tempProduct;
            }
        }
        return $products;
    }
    
    /**
     * @param $productID
     * @return Product
     */
    public function getProduct($productID)
    {
        APIWrapper::verifyReadiness();
        $requestURL = "https://app.fetchapp.com/api/v2/products/" . $productID;
        $product = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($product, "SimpleXMLElement")) {
            $tempProduct = new Product();
			$tempProduct->setProductID($product->id);
			$tempProduct->setSKU($product->sku);
			$tempProduct->setName($product->name);
			$tempProduct->setPrice($product->price);
			$tempProduct->setCurrency(Currency::getValue($product->currency));
			$tempProduct->setOrderCount($product->order_count);
			$tempProduct->setDownloadCount($product->download_count);
			$tempProduct->setPaypalAddToCartLink($product->paypal_add_to_cart_link['href']);
			$tempProduct->setPaypalBuyNowLink($product->paypal_buy_now_link['href']);
			$tempProduct->setPaypalViewCartLink($product->paypal_view_cart_link['href']);
			$tempProduct->setCreationDate(new \DateTime($product->created_at));
			$tempProduct->setFilesUri($product->files_uri);
			$tempProduct->setDownloadsUri($product->downloads_uri);
        }
        return $tempProduct;
    }
	
    /**
	 * @param $itemsPerPage
     * @param $pageNumber
     * @return OrderDownload[]
     */     
    public function getDownloads($itemsPerPage = -1, $pageNumber = -1)
    {
        APIWrapper::verifyReadiness();
        $downloads = array();
        
        $requestURL = "https://app.fetchapp.com/api/v2/downloads.xml?";

        if ($itemsPerPage != -1) {
            $requestURL .= "per_page=" . $itemsPerPage;
        }
        
        if ($pageNumber != -1) {
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        }
        
        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($results, "SimpleXMLElement")) {
            foreach ($results->download as $download) {
                $tempDownload = new OrderDownload();
                
                $tempDownload->setDownloadID($download->id);
                $tempDownload->setFileName($download->filename);
                $tempDownload->setSKU($download->product_sku);
                $tempDownload->setOrderID($download->order_id);
                $tempDownload->setIPAddress($download->ip_address);
			   	$tempDownload->setDownloadedOn(new \DateTime($download->downloaded_at));
				$tempDownload->setSizeInBytes($download->size_bytes);

                $downloads[] = $tempDownload;
            }

        }
        return $downloads; 
    }
    
    /**
	 * @param $itemsPerPage
     * @param $pageNumber
     * @return FileDetail[]
     */     
    public function getFiles($itemsPerPage = -1, $pageNumber = -1)
    {
        APIWrapper::verifyReadiness();
        $files = array();
        
        $requestURL = "https://app.fetchapp.com/api/v2/files.xml?";

        if ($itemsPerPage != -1) {
            $requestURL .= "per_page=" . $itemsPerPage;
        }
        
        if ($pageNumber != -1) {
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        }
        
        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");
        if (is_a($results, "SimpleXMLElement")) {
            foreach ($results->file as $file) {
                $tempFile = new FileDetail();
                
                $tempFile->setFileID($file->id);
                $tempFile->setFileName($file->filename);
                $tempFile->setSizeInBytes($file->size_bytes);
                $tempFile->setContentType($file->content_type);
                $tempFile->setPermalink($file->permalink);
                $tempFile->setUrl($file->url);
                $tempFile->setType($file->type);

                $files[] = $tempFile;
            }

        }
        return $files; 
    }

    /**
     * @return bool True on success, otherwise False.
     */
    public function getNewToken()
    {
        APIWrapper::verifyReadiness();
        $success = false;
        $result = APIWrapper::makeRequest("https://app.fetchapp.com/api/v2/new_token", "GET");
        $this->setAuthenticationToken($result[0]);
        $success = true;
        return $success;
    }
}