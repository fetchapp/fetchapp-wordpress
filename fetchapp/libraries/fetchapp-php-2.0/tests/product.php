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
	$products = $fetch->getProducts();
	foreach($products as $product):
		//var_dump($product);
		$downloads = $product->getDownloads();
		//var_dump($downloads);
		
		$files = $product->getFiles();
		
		//var_dump($files);
		
		$stats = $product->getStatistics();
		
		//var_dump($stats);
		
	//	$product->setSKU('test1234566'.rand(0,5) );
		//var_dump($product->create($files) );
		echo "\n\nUprdate\n\n";

		$product->setPrice(2 );

		var_dump($product->create() );

	endforeach;
	$files = $fetch->getFiles();

	$product = new FetchApp\API\Product();
	$product->setSKU('test123');
	$product->setPrice(3);
	$product->setName('testing');
	var_dump($product->create($files) );
	echo "here";
}
catch (Exception $e){
// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    echo $e->getMessage();
}
