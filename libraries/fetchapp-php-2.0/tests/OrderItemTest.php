<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\Order;
use FetchApp\API\OrderItem;
use FetchApp\API\FileDetail;

final class OrderItemTest extends FetchAppBaseTest
{
    public static $baseClass = OrderItem::class;

    public function testClass(): void
    {
        $item = self::testSingle();
        
        $this->assertInstanceOf(
            self::$baseClass,
            $item
        );
    }

    public function testSingle(): OrderItem
    {
        $order = $this->_getTestOrder();
        $items = $order->getItems(); // Get the existing order items

        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertInstanceOf(
            self::$baseClass,
            $items[0]
        );
        return $items[0];
    }

    public function testFiles(): Array{
        return $this->_doFileTest();
    }

    public function testDownloads(): Array{
        return $this->_doDownloadTest();
    }
}
