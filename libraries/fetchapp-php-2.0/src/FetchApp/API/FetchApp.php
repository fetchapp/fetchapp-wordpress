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
        $results = APIWrapper::makeRequest("/account", "GET");

        if (is_object($results) && is_object($results->account)) :
            $detail->loadFromJSON($results->account);
        endif;

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
        $requestURL = "/orders?";

        if ($status != OrderStatus::All) :
            $requestURL .= "status=" . strtolower(OrderStatus::getName($status));
        endif;

        if ($itemsPerPage != -1) :
            $requestURL .= ($status != OrderStatus::All) ? "&" : "";
            $requestURL .= "page_size=" . $itemsPerPage;
        endif;

        if ($pageNumber != -1) :
            $requestURL .= ($status != OrderStatus::All || $itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        endif;

        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($results)) :
            foreach ($results->orders as $order) :
                $tempOrder = new Order();
                $tempOrder->loadFromJSON($order);
                $orders[] = $tempOrder;
            endforeach;
        endif;

        return $orders;
    }

    /**
     * @param $orderID
     * @return Order
     */
    public function getOrder($vendorID)
    {
        APIWrapper::verifyReadiness();
        $requestURL = "/vorders/" . $vendorID;

        $results = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($results) && is_object($results->order)) :
            $order = $results->order;
            $tempOrder = new Order();
            $tempOrder->loadFromJSON($order);
            return $tempOrder;
        else:
            return false;
        endif;
    }

    /**
     * @param $orderID
     * @return Order
     */
    public function getOrderByID($orderID)
    {
        APIWrapper::verifyReadiness();
        $requestURL = "/orders/" . $orderID;

        $results = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($results) && is_object($results->order)) :
            $order = $results->order;
            $tempOrder = new Order();
            $tempOrder->loadFromJSON($order);
            return $tempOrder;
        else:
            return false;
        endif;
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
        $requestURL = "/products?";

        if ($itemsPerPage != -1) {
            $requestURL .= "page_size=" . $itemsPerPage;
        }
        
        if ($pageNumber != -1) {
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        }
        
        $requestURL = rtrim($requestURL, '?');
        $results = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($results)) {
            foreach ($results->products as $product) {
                $tempProduct = new Product();
                $tempProduct->loadFromJSON($product);
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

        if(! $productID):
            return false;
        endif;
        
        $requestURL = "/products/" . $productID;

        $response = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($response) && is_object($response->product)) :
            $product = $response->product;
            $tempProduct = new Product();
            $tempProduct->loadFromJSON($product);
            return $tempProduct;
        else:
            return false;
        endif;
    }

    /**
     * @param $productID
     * @return Product
     */
    public function getProductBySku($productSku)
    {
        APIWrapper::verifyReadiness();

        if(! $productSku):
            return false;
        endif;
        
        $requestURL = "/skuproducts/" . $productSku;

        $response = APIWrapper::makeRequest($requestURL, "GET");

        if (is_object($response) && is_object($response->product)) :
            $product = $response->product;
            $tempProduct = new Product();
            $tempProduct->loadFromJSON($product);
            return $tempProduct;
        else:
            return false;
        endif;
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

        $requestURL = "/downloads?";

        if ($itemsPerPage != -1) :
            $requestURL .= "page_size=" . $itemsPerPage;
        endif;
        
        if ($pageNumber != -1) :
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        endif;
        
        $requestURL = rtrim($requestURL, '?');
        $response = APIWrapper::makeRequest($requestURL, "GET");
        if (is_object($response) && is_array($response->downloads)) :
            foreach ($response->downloads as $json_download) :
                $tempDownload = new OrderDownload();
                $tempDownload->loadFromJSON($json_download);
                $downloads[] = $tempDownload;
            endforeach;
        endif;
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
        
        $requestURL = "/files?";

        if ($itemsPerPage != -1) :
            $requestURL .= "page_size=" . $itemsPerPage;
        endif;
        
        if ($pageNumber != -1) :
            $requestURL .= ($itemsPerPage != -1) ? "&" : "";
            $requestURL .= "page=" . $pageNumber;
        endif;
        
        $requestURL = rtrim($requestURL, '?');

        $response = APIWrapper::makeRequest($requestURL, "GET");
        if (is_object($response) && is_array($response->files)) :
            foreach ($response->files as $json_file) :
                $tempFile = new FileDetail();
                $tempFile->loadFromJSON($json_file);
                $files[] = $tempFile;
            endforeach;
        endif;
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

    /**
     * @param bool $ssl_mode_bool
     */
    public function setSSLMode($ssl_mode_bool)
    {
        APIWrapper::setSSLMode($ssl_mode_bool);
    }
}