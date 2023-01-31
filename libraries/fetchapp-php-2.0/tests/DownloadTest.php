<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\Product;
use FetchApp\API\OrderDownload;

final class DownloadTest extends FetchAppBaseTest
{
    public static $baseClass = OrderDownload::class;

    public function testClass(): void
    {
        $item = self::testSingle();

        $this->assertInstanceOf(
            self::$baseClass,
            $item
        );
    }

    public function testSingle(): OrderDownload
    {
        $fetch = self::$fetch;
        $downloads = $fetch->getDownloads(1); // Grabs all files

        $this->assertIsArray($downloads);
        $this->assertNotEmpty($downloads);

        $item = array_pop($downloads);

        $this->assertInstanceOf(
            self::$baseClass,
            $item
        );
        return $item;
    }

    public function testList(): void
    {
        $fetch = self::$fetch;

        $downloads = $fetch->getDownloads(); // Grabs all downloads
        $this->assertIsArray($downloads);
        $this->assertNotEmpty($downloads);
        $this->assertCount(50, $downloads);


        $downloads = $fetch->getDownloads(10, 2); // Grabs downloads, 10 per page, page 2.

        $this->assertIsArray($downloads);
        $this->assertNotEmpty($downloads);
        $this->assertCount(10, $downloads);
    }

    public function testProductDownloads(): void
    {
        $fetch = self::$fetch;
        $product = $fetch->getProduct($_ENV['TEST_SINGLE_PRODUCT_ID']);
        $downloads = $product->getDownloads();
        $this->assertIsArray($downloads);
        $this->assertNotEmpty($downloads);

        $this->assertInstanceOf(
            OrderDownload::class,
            $downloads[0]
        );
    }

    public function testOrderItemDownloads(): void
    {
        $item = $this->_getTestOrderItem();

        $downloads = $item->getDownloads();
        $this->assertIsArray($downloads);
        $this->assertNotEmpty($downloads);

        $this->assertInstanceOf(
            OrderDownload::class,
            $downloads[0]
        );
    }
}
