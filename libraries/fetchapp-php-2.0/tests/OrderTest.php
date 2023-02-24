<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\OrderStatus;
use FetchApp\API\Order;
use FetchApp\API\OrderDownload;
use FetchApp\API\OrderItem;
use FetchApp\API\Currency;

final class OrderTest extends FetchAppBaseTest
{
    public function testList(): void
    {
        $fetch = self::$fetch;

        $orders = $fetch->getOrders(); // Grabs all orders (potentially HUGE!)

        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);

        $orders = $fetch->getOrders(OrderStatus::All, 10, 0); // Grabs orders of all types, 50 per page, page 4.
        $this->assertCount(10, $orders);

        $orders = $fetch->getOrders(OrderStatus::Expired); // Grabs all expired orders.
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);

        $orders = $fetch->getOrders(OrderStatus::Open); // Grabs all open orders
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);
    }

    public function testClass(): void
    {
        $fetch = self::$fetch;
        $order = $fetch->getOrderByID($_ENV['TEST_SINGLE_ORDER_ID']);

        $this->assertInstanceOf(
            Order::class,
            $order
        );
    }

    public function testSingleOrderByOrderID(): void
    {
        $fetch = self::$fetch;
        $order = $fetch->getOrderByID($_ENV['TEST_SINGLE_ORDER_ID']);

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $this->assertSame((int)$_ENV['TEST_SINGLE_ORDER_ID'], $order->getOrderId());
        $this->assertSame($_ENV['TEST_SINGLE_ORDER_VENDOR_ID'], $order->getVendorID());
    }

    public function testSingleOrderByVendorId(): void
    {
        $fetch = self::$fetch;

        $order = $fetch->getOrder($_ENV['TEST_SINGLE_ORDER_VENDOR_ID']);

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $this->assertSame((int)$_ENV['TEST_SINGLE_ORDER_ID'], $order->getOrderId());
        $this->assertSame($_ENV['TEST_SINGLE_ORDER_VENDOR_ID'], $order->getVendorID());
    }

    public function testCreate(): Order
    {
        $fetch = self::$fetch;

        $order = new Order();
        $random_vendor_id = "M010".rand();

        // // PRC TODO: SETTING ID IS IGNORED
        // // $order->setOrderID("B008");

        $order->setFirstName("James");
        $order->setLastName("Bond");
        $order->setEmailAddress("007@prcapps.com");

        $order->setVendorID($random_vendor_id);

        $order->setCurrency(Currency::GBP);
        $order->setCustom1("Herp");
        $order->setCustom3("Derp");
        $order->setExpirationDate(new \DateTime("2015/12/24"));
        $order->setDownloadLimit(12);

        $items = array();
        // Add items to the item array
        $order_item = new OrderItem();
        $order_item->setItemID(3464729);
        // $order_item->setSKU('TestSKU');
        array_push($items, $order_item);

        $response = $order->create($items, false);

        $this->assertTrue($response);
        $this->assertNotNull($order->getOrderID());
        $this->assertSame($random_vendor_id, $order->getVendorID());
        // PRC - Can add extra fields

        // Create a product with the same Vendor ID should fail
        $null_order = new Order();

        $null_order->setFirstName("James");
        $null_order->setLastName("Bond");
        $null_order->setEmailAddress("007@prcapps.com");

        $null_order->setVendorID($random_vendor_id);
        $null_order->setCurrency(Currency::GBP);

        $response = $null_order->create($items, false);

        $this->assertSame(0, $null_order->getOrderID());

        return $order;
    }

    public function testUpdate(): void
    {
        $fetch = self::$fetch;

        $order = $this->testCreate();
        $items = $order->getItems(); // Get the existing order items


        $new_first_name = "JamesUPDATE";
        $new_last_name = "BondUPDATE";
        $new_email = "008@prcapps.com";
        $new_vendor_id = "UPDATE_VID".time();
        // $new_order_id = "B008".time();

        // // PRC TODO: SETTING ID Breaks call to get items
        // $order->setOrderID($new_order_id);

        $order->setFirstName($new_first_name);
        $order->setLastName($new_last_name);
        $order->setEmailAddress($new_email);
        $order->setVendorID($new_vendor_id);
        // $order->setCurrency(Currency::GBP);
        // $order->setCustom1("Herp");
        // $order->setCustom3("Derp");
        // $order->setExpirationDate(new DateTime("2015/12/24"));
        $order->setDownloadLimit(123);

        $response = $order->update($items, false);

        // $this->assertTrue($response);
        $this->assertNotFalse($order);
        $this->assertNotNull($order->getOrderID());
        $this->assertSame($new_first_name, $order->getFirstName());
        $this->assertSame($new_last_name, $order->getLastName());
        $this->assertSame($new_email, $order->getEmailAddress());
        $this->assertSame($new_vendor_id, $order->getVendorID());
    }

    public function testDelete(): void
    {
        $fetch = self::$fetch;

        $order = $this->testCreate();
        $delete_order_id = $order->getOrderID();
        $order = $fetch->getOrderByID($delete_order_id);

        $this->assertNotFalse($order);

        $response = $order->delete();
        $order = $fetch->getOrderByID($delete_order_id);
        $this->assertFalse($order);
    }

    // PRC TODO
    // PRC - Note this functionality is removed
    public function testOrderStats(): void
    {
        $fetch = self::$fetch;
        $this->assertSame(true, true);
    }

    public function testDownloads(): Array
    {
        $order = $this->_getTestOrder();

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $downloads = $order->getDownloads();

        $this->assertNotEmpty($downloads);

        // $download = $downloads[0];
        foreach($downloads as $download):
            $this->assertInstanceOf(
                OrderDownload::class,
                $download
            );
        endforeach;

        return $downloads;
    }

    public function testOrderExpire(): void
    {
        $order = $this->_getTestOrder();

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $response = $order->expire();

        $this->assertSame($response->order->status, 'expired');

        $order = $this->_getTestOrder();

        $this->assertSame($order->getStatus() , OrderStatus::getValue('expired') );
    }

    public function testOrderReopen(): void
    {
        $order = $this->_getTestOrder();

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $response = $order->reopen();

        $this->assertSame($response->order->status, 'open');

        $order = $this->_getTestOrder();

        $this->assertSame($order->getStatus() , OrderStatus::getValue('open') );
    }

    public function testOrderResendEmail(): void
    {
        $order = $this->_getTestOrder();

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $response = $order->reopen();

        $this->assertSame($response->order->status, 'open');

        $order = $this->_getTestOrder();

        $this->assertSame($order->getStatus() , OrderStatus::getValue('open') );
    }

    public function testOrderSendDownloadEmail(): void
    {
        $order = $this->_getTestOrder();

        $this->assertInstanceOf(
            Order::class,
            $order
        );

        $response = $order->sendDownloadEmail();

        $this->assertSame($response->order->status, 'open');

        $order = $this->_getTestOrder();

        $this->assertSame($order->getStatus() , OrderStatus::getValue('open') );
    }
}
