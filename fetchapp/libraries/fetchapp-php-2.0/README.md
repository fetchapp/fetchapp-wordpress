fetchapp-php-2.0
================

A PHP library for version 2.0 of the FetchApp API

# Proposed Syntax

## Getting Account Information

```php

use FetchApp\API\FetchApp;

// Create a new FetchApp instance
$fetch = new FetchApp();

// Set the Authentication data (needed for all requests)
$fetch->setAuthenticationKey("demokey");
$fetch->setAuthenticationToken("demotoken");
try{
// Let's grab our Account data to make sure that everything is working!
    $account = $fetch->getAccountDetails();//    That was easy!

// Let's write some of the available Data to the page!
    echo $account->getAccountID();
    echo $account->getAccountName();
    echo $account->getBillingEmail();
    echo $account->getEmailAddress();
    echo $account->getURL();
    echo $account->getItemDownloadLimit();
    echo $account->getOrderExpirationInHours();
}
catch (Exception $e){
// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
```

## Getting Order Information
### Getting All Orders
```php
use FetchApp\API\FetchApp;
use FetchApp\API\OrderStatus;

// Create a new FetchApp instance
$fetch = new FetchApp();

// Set the Authentication data (needed for all requests)
$fetch->setAuthenticationKey("demokey");
$fetch->setAuthenticationToken("demotoken");;
try{
    // Let's grab our Orders!
    $orders = $fetch->getOrders(); // Grabs all orders (potentially HUGE!)
                    // or
    $orders = $fetch->getOrders(OrderStatus::All, 50, 4); // Grabs orders of all types, 50 per page, page 4.
                    // or
    $orders = $fetch->getOrders(OrderStatus::Expired); // Grabs all expired orders.
                    // or
    $orders = $fetch->getOrders(OrderStatus::Open); // Grabs all open orders
}
catch (Exception $e){
    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
// Now let's print our results!
foreach ($orders as $order) {
    echo $order->getOrderID().PHP_EOL;
    echo $order->getVendorID().PHP_EOL;
    echo $order->getFirstName().PHP_EOL;
    echo $order->getLastName().PHP_EOL;
    echo $order->getEmailAddress().PHP_EOL;
    echo $order->getTotal().PHP_EOL;
    echo $order->getCurrency().PHP_EOL;
    echo $order->getStatus().PHP_EOL;
    echo $order->getProductCount().PHP_EOL;
    echo $order->getDownloadCount().PHP_EOL;
    $expirationDate = $order->getExpirationDate();
    // Since ExpirationDate is a DateTime, we need to print it with a format.
    echo $expirationDate->format('F j, Y').PHP_EOL;
    echo $order->getDownloadLimit().PHP_EOL;
    echo $order->getCustom1().PHP_EOL;
    echo $order->getCustom2().PHP_EOL;
    echo $order->getCustom3().PHP_EOL;
    $creationDate = $order->getCreationDate();
    // Since CreationDate is a DateTime, we need to print it with a format.
    echo $creationDate->format('F j, Y').PHP_EOL;
}
```

### Getting a Single Order
```php
use FetchApp\API\FetchApp;
use FetchApp\API\OrderStatus;

// Create a new FetchApp instance
$fetch = new FetchApp();

// Set the Authentication data (needed for all requests)
$fetch->setAuthenticationKey("demokey");
$fetch->setAuthenticationToken("demotoken");
try{
    // Let's grab our Orders!
    $order = $fetch->getOrder("B007");
}
catch (Exception $e){
    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
// Now let's print our result!
echo $order->getOrderID().PHP_EOL;
echo $order->getVendorID().PHP_EOL;
echo $order->getFirstName().PHP_EOL;
echo $order->getLastName().PHP_EOL;
echo $order->getEmailAddress().PHP_EOL;
echo $order->getTotal().PHP_EOL;
echo $order->getCurrency().PHP_EOL;
echo $order->getStatus().PHP_EOL;
echo $order->getProductCount().PHP_EOL;
echo $order->getDownloadCount().PHP_EOL;
$expirationDate = $order->getExpirationDate();
// Since ExpirationDate is a DateTime, we need to print it with a format.
echo $expirationDate->format('F j, Y').PHP_EOL;
echo $order->getDownloadLimit().PHP_EOL;
echo $order->getCustom1().PHP_EOL;
echo $order->getCustom2().PHP_EOL;
echo $order->getCustom3().PHP_EOL;
$creationDate = $order->getCreationDate();
// Since CreationDate is a DateTime, we need to print it with a format.
echo $creationDate->format('F j, Y').PHP_EOL;
```

### Creating an Order
```php
use FetchApp\API\Currency;
use FetchApp\API\FetchApp;
use FetchApp\API\Order;
use FetchApp\API\OrderItem;

// Create a new FetchApp instance
$fetch = new FetchApp();

// Set the Authentication data (needed for all requests)
$fetch->setAuthenticationKey("demokey");
$fetch->setAuthenticationToken("demotoken");

try{
    $order = new Order();
    $order->setOrderID("B008");
    $order->setFirstName("James");
    $order->setLastName("Bond");
    $order->setEmailAddress("007@mi6.com");
    $order->setVendorID("M002");
    $order->setCurrency(Currency::GBP);
    $order->setCustom1("Herp");
    $order->setCustom3("Derp");
    $order->setExpirationDate(new DateTime("2015/12/24"));
    $order->setDownloadLimit(12);
    $items = array();
    // Add items to the item array

    $response = $order->create($items, false);
}
catch (Exception $e){
    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
```
