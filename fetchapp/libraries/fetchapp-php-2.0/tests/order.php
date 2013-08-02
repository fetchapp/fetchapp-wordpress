<?php

function __autoload($classname){
	$classes = explode("\\", $classname);

	$last_class = $classes[count($classes) - 1];
	require "../src/{$last_class}.class.php";
}

date_default_timezone_set('America/New_York');


// Create a new FetchApp instance
$fetch = new FetchApp\API\FetchApp();

// Set the Authentication data (needed for all requests)

$fetch_token = 'ich1ohngutho';
	$fetch_key = 'prcapps';

$fetch->setAuthenticationKey($fetch_key);
$fetch->setAuthenticationToken($fetch_token);
try{
// Let's grab our Account data to make sure that everything is working!
	$orders = $fetch->getOrders();
	$products = $fetch->getProducts();
	
	$test_order_item = new FetchApp\API\ORderItem();
	$test_order_item->setSKU('test123');
//	$test_order_item->setDownloadsRemianing('test123');
	

	foreach($orders as $order):
		$downloads = $order->getDownloads();
				
		$stats = $order->getStatistics();
		
		//var_dump($stats);
		
	//	$order->setSKU('test1234566'.rand(0,5) );
		//var_dump($product->create($files) );
		echo "\n\nUprdate\n\n";

		$order->setFirstName('Joe' );

		var_dump($order->update(array($test_order_item) ) );

	endforeach;

	$order = new FetchApp\API\Order();
	$order->setFirstName('Patrick');
	$order->setLastName('Conant');
	$order->setEmailAddress('conantp@gmail.com');
	var_dump($order->create(array($test_order_item) ) );
	echo "here";
}
catch (Exception $e){
// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
