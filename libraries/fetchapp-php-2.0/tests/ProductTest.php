<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\Product;
use FetchApp\API\Currency;
use FetchApp\API\FileDetail;
use FetchApp\API\OrderDownload;

final class ProductTest extends FetchAppBaseTest
{
    public static $baseClass = Product::class;

    public function testClass(): void
    {
        $fetch = self::$fetch;
        $product = $fetch->getProduct($_ENV['TEST_SINGLE_PRODUCT_ID']);

        $this->assertInstanceOf(
            Product::class,
            $product
        );
    }

    public function testList(): void
    {
        $fetch = self::$fetch;

        $products = $fetch->getProducts(); 

        $this->assertIsArray($products);
        $this->assertNotEmpty($products);

        $products = $fetch->getProducts(2, 2);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products);
        $this->assertCount(2, $products);
    }

    public function testSingle(): Product
    {
        $fetch = self::$fetch;
        $product = $fetch->getProduct($_ENV['TEST_SINGLE_PRODUCT_ID']);

        $this->assertSame((int)$_ENV['TEST_SINGLE_PRODUCT_ID'], $product->getProductID());
        $this->assertSame($_ENV['TEST_SINGLE_PRODUCT_SKU'], $product->getSKU());
        $this->assertSame($_ENV['TEST_SINGLE_PRODUCT_NAME'], $product->getName());
        return $product;
    }

    public function testCreate(): Product
    {
        $fetch = self::$fetch;

        $random_sku = "PRCTEST".time();
        $random_name = "PRC Test Product ".time();
        $product = new Product();
        $product->setSKU($random_sku);
        $product->setName($random_name);
        $product->setPrice(1.00);
        $product->setCurrency(Currency::USD);

        $files = array();
        $item_urls = array(array("url" => "http://s3.aws/download.mp3", "name" => "audio"));

        $response = $product->create($files, $item_urls);

        $this->assertTrue($response);
        $this->assertNotNull($product->getProductID());
        $this->assertSame($random_sku, $product->getSKU());
        $this->assertSame($random_name, $product->getName());

        // Create a product with the same SKU should fail
        $null_product = new Product();
        $null_product->setSKU($random_sku);
        $null_product->setName($random_name);
        $null_product->setPrice(1.00);
        $null_product->setCurrency(Currency::USD);

        $response = $null_product->create($files, $item_urls);
        $this->assertNull($null_product->getProductID());

        return $product;
    }

    public function testUpdate(): Product
    {
        $fetch = self::$fetch;

        $product = $this->testCreate();

        $random_sku = "PRCTESTUPDATE".time();
        $random_name = "PRCTest  Product UPDATE ".time();

        $product->setSKU($random_sku);
        $product->setName($random_name);
        $product->setPrice(2.00);
        $product->setCurrency(Currency::GBP);

        $files = $product->getFiles();

        $item_urls = array(array("url" => "http://s3.aws/download_update.mp3", "name" => "audio"));
        $response = $product->update($files, $item_urls);
        $this->assertTrue($response);
        $this->assertNotNull($product->getProductID());
        $this->assertSame($random_sku, $product->getSKU());
        $this->assertSame($random_name, $product->getName());
        return $product;
    }

    public function testDelete(): void
    {
        $fetch = self::$fetch;

        $product = $this->testCreate();
        $product_id = $product->getProductID();
        $product = $fetch->getProduct($product_id);

        $this->assertNotFalse($product);

        $response = $product->delete();
        $product = $fetch->getProduct($product_id);
        $this->assertFalse($product);
    }

    public function testFiles(): Array{
        return $this->_doFileTest();
    }

    public function testDownloads(): Array{
       return $this->_doDownloadTest();
    }
}
