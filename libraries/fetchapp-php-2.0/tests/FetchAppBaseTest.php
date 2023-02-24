<?php declare(strict_types=1);

namespace FetchApp\Tests;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use FetchApp\API\FetchApp;
use FetchApp\API\FileDetail;
use FetchApp\API\Order;
use FetchApp\API\OrderDownload;
use FetchApp\API\OrderItem;
use FetchApp\API\Product;

class FetchAppBaseTest extends TestCase
{
    public static $fetch;

    public static $baseClass;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/..');
        $dotenv->load();

        self::$fetch = new FetchApp();
        self::$fetch->setAuthenticationKey($_ENV['FETCH_API_KEY']);
        self::$fetch->setAuthenticationToken($_ENV['FETCH_API_TOKEN']);
    }

    public static function tearDownAfterClass(): void
    {
        self::$fetch = null;
    }

    public function testFetchAppClass(): void
    {
        $fetch = self::$fetch;
        $this->assertInstanceOf(
            FetchApp::class,
            $fetch
        );
    }

    // public function testClass(): void
    // {
    //     $item = self::testSingle();
    //     var_dump($item);
    //     $this->assertInstanceOf(
    //         self::$baseClass,
    //         $item
    //     );
    // }

    public function _getTestOrder(): Order{
        $fetch = self::$fetch;
            
        // $order = $fetch->getOrderByID($_ENV['TEST_SINGLE_ORDER_ID']);
        $order = $fetch->getOrder($_ENV['TEST_SINGLE_ORDER_VENDOR_ID']);
        $this->assertInstanceOf(
            Order::class,
            $order
        );
        return $order;
    }

    public function _getTestOrderItem(): OrderItem{
        $order = $this->_getTestOrder();
        $items = $order->getItems(); // Get the existing order items

        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertInstanceOf(
            OrderItem::class,
            $items[0]
        );
        return $items[0];
    }

    public function _getTestProduct(): Product{
        $fetch = self::$fetch;
        $product = $fetch->getProduct($_ENV['TEST_SINGLE_PRODUCT_ID']);

        $this->assertSame((int)$_ENV['TEST_SINGLE_PRODUCT_ID'], $product->getProductID());
        $this->assertSame($_ENV['TEST_SINGLE_PRODUCT_SKU'], $product->getSKU());
        $this->assertSame($_ENV['TEST_SINGLE_PRODUCT_NAME'], $product->getName());
        return $product;
    }

    public function _doFileTest(): Array{
        $item = $this->testSingle();

        $files = $item->getFiles();

        $this->assertNotEmpty($files);

        foreach($files as $file):
            $this->assertInstanceOf(
                FileDetail::class,
                $file
            );
        endforeach;

        return $files;
    }

    public function _doDownloadTest(): Array
    {
        $item = $this->testSingle();

        $downloads = $item->getDownloads();

        $this->assertNotEmpty($downloads);

        foreach($downloads as $download):
            $this->assertInstanceOf(
                OrderDownload::class,
                $download
            );
        endforeach;
        
        return $downloads;
    }
}
