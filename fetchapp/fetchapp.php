<?php
/**
 * @package FetchApp
 * @version 1.0
 */
/*
Plugin Name: Fetch App
Plugin URI: http://www.prcapps.com/
Description: Fetch App Integration for WooCommerce
Author: Patrick Conant
Version: 1.0
Author URI: http://www.prcapps.com/
*/





require_once('libraries/fetchapp-php-2.0/src/FetchApp.class.php');
require_once('libraries/fetchapp-php-2.0/src/APIWrapper.class.php');
require_once('libraries/fetchapp-php-2.0/src/AccountDetail.class.php');
require_once('libraries/fetchapp-php-2.0/src/Currency.class.php');
require_once('libraries/fetchapp-php-2.0/src/Order.class.php');
require_once('libraries/fetchapp-php-2.0/src/OrderItem.class.php');
require_once('libraries/fetchapp-php-2.0/src/OrderStatus.class.php');
require_once('libraries/fetchapp-php-2.0/src/Product.class.php');
require_once('libraries/fetchapp-php-2.0/src/ProductStatistic.class.php');
require_once('libraries/fetchapp-php-2.0/src/FileDetail.class.php');

//require_once('/var/www/vhosts/fetchapp2.prcapps.com/public_html/wp-content/plugins/woocommerce/classes/class-wc-order.php');

//use FetchApp\API\FetchApp;

//if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :

	if ( ! class_exists( 'WC_FetchApp' ) ) :

		class WC_FetchApp {
			public function __construct() {
				$this->debug = true;
				//add_action( 'wp_enqueue_scripts', array( $this, 'wc_fetchapp_styles' ) );

				// Init settings
				$this->settings = array(
					array(
						'name' 		=> __( 'FetchApp', 'woocommerce-fetchapp' ),
						'desc'		=> __( 'FetchApp Account Details', 'woocommerce-fetchapp' ),
						'type' 		=> 'title',
						'id' 		=> 'wc_pdc_options'
					),
					array(
						'name' 		=> __( 'Key', 'woocommerce-fetchapp' ),
						'desc' 		=> __( 'Enter your FetchApp Key', 'woocommerce-fetchapp' ),
						'id' 		=> 'wc_fetchapp_key',
						'type' 		=> 'text',
						'default'	=> ''
					),
					array(
						'name' 		=> __( 'Token', 'woocommerce-fetchapp' ),
						'desc' 		=> __( 'Enter your FetchApp Token', 'woocommerce-fetchapp' ),
						'id' 		=> 'wc_fetchapp_token',
						'type' 		=> 'text',
						'default'	=> ''
					),
					/*
					array(
						'title' 	=> __( 'Related Products / Upsells Columns', 'woocommerce-product-details-customiser' ),
						'desc'		=> __( 'The number of columns that related products / upsells are arranged in to on product detail pages', 'woocommerce-product-details-customiser' ),
						'id' 		=> 'wc_pdc_columns',
						'default'	=> '2',
						'type' 		=> 'select',
						'options' 	=> array(
							'2'  	=> __( '2', 'woocommerce-product-details-customiser' ),
							'3' 	=> __( '3', 'woocommerce-product-details-customiser' ),
							'4' 	=> __( '4', 'woocommerce-product-details-customiser' ),
							'5' 	=> __( '5', 'woocommerce-product-details-customiser' )
						)
					),*/
					array( 'type' => 'sectionend', 'id' => 'woocommerce-fetchapp' ),
				);

				// Default options
				add_option( 'wc_fetchapp_key', '' );
				add_option( 'wc_fetchapp_token', '' );

				// Admin
				$this->makeSettingsScreen();

				//	$this->fetch_token = 'ich1ohngutho';
				//	$this->fetch_key = 'prcapps';

				if ( get_option( 'wc_fetchapp_key' ) ):
					$this->fetch_key  = get_option( 'wc_fetchapp_key' );
				endif;

				if ( get_option( 'wc_fetchapp_token' ) ):
					$this->fetch_token  = get_option( 'wc_fetchapp_token' );
				endif;

				$this->fetchApp = new FetchApp\API\FetchApp();

				$this->fetchApp->setAuthenticationKey($this->fetch_key);
				$this->fetchApp->setAuthenticationToken($this->fetch_token);

				//$this->syncAllProducts();
				//$this->syncAllOrders();

				//$orders = $this->pullOrdersFromFetch();
				//var_dump($orders);
				//$account = $this->fetchApp->getAccountDetails();
				//var_dump($account);

			}

			/* Woocommerce FetchApp Actions */
			public function syncAllProducts(){
				$wc_products = $this->getWCProducts(); /* Get WC Products */

				foreach($wc_products as $product):
					/* Push to Fetch */
					$this->pushProductToFetch($product);
				endforeach;


				$fetch_products = $this->pullProductsFromFetch(); /* Need Product API Code */

				foreach($fetch_products as $product):
					$this->insertFetchProduct($product);
				endforeach;
			}

			public function syncAllOrders(){
				$wc_orders = $this->getWCOrders(); /* Get WC Products */

				foreach($wc_orders as $order):
					/* Push to Fetch */
					$this->pushOrderToFetch2($order);
				endforeach;

				$fetch_orders = $this->pullOrdersFromFetch(); /* Need Product API Code */

				foreach($fetch_orders as $order):
					$this->insertFetchOrder($order);
				endforeach;
			}

			public function pushOrderToFetch($wc_order){

				try{
					/* Remap Order Fields */
					$order = new FetchApp\API\Order();

				    //$wc_order->order_custom_fields = get_post_meta( $wc_order->id );


				    $order->setOrderID("wc_".$wc_order->id);
				    $order->setFirstName($wc_order->order_custom_fields['_billing_first_name'][0]);
				    $order->setLastName($wc_order->order_custom_fields['_billing_last_name'][0]);
				    $order->setEmailAddress($wc_order->order_custom_fields['_billing_email'][0]);




				    $order->setVendorID($wc_order->id);
				    // need to set currency variable
				    $order->setCurrency(FetchApp\API\Currency::USD);
	//			    $order->setCustom1("Herp");
	//			    $order->setCustom3("Derp");
	//			    $order->setExpirationDate(new DateTime("2015/12/24"));
	//			    $order->setDownloadLimit(12);
				    $items = array();

				   

			    	foreach($wc_order->get_items() as $item):
						$product_id = $item['product_id'];
						$qty = $item['qty'];

						$product_factory = new WC_Product_Factory();
						$product = $product_factory->get_product($product_id);

						$fetch_sku = get_post_meta($product_id, '_fetchapp_id', true);

						// If there's a fetch SKU set, we need to push this order up
						if($fetch_sku):
					    	$order_item = new FetchApp\API\OrderItem();
							$order_item->setSKU($fetch_sku);
							$items[] = $order_item;
						endif;
					endforeach;

					/* Push to Fetch */
				    $response = $order->create($items);

				    if($this->debug):
					    var_dump($response);
					endif;
				}
				catch (Exception $e){

				    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
				    echo $e->getMessage();
				}

			}

			public function pushOrderToFetch2($wc_order){

				try{
					/* Remap Order Fields */
					$order = new FetchApp\API\Order();

				    $wc_order->order_custom_fields = get_post_meta( $wc_order->ID );


				    $order->setOrderID("wc_".$wc_order->id);
				    $order->setFirstName($wc_order->order_custom_fields['_billing_first_name'][0]);
				    $order->setLastName($wc_order->order_custom_fields['_billing_last_name'][0]);
				    $order->setEmailAddress($wc_order->order_custom_fields['_billing_email'][0]);

				    $order->setVendorID($wc_order->id);
				    // need to set currency variable
				    $order->setCurrency(FetchApp\API\Currency::USD);
	//			    $order->setCustom1("Herp");
	//			    $order->setCustom3("Derp");
	//			    $order->setExpirationDate(new DateTime("2015/12/24"));
	//			    $order->setDownloadLimit(12);
				    $items = array();

				    $order_items = $this->getWCOrderItems($wc_order);
				   	
			    	foreach($order_items as $item):
						$product_id = $item['product_id'];
						$qty = $item['qty'];

						$product_factory = new WC_Product_Factory();
						$product = $product_factory->get_product($product_id);

						$fetch_sku = get_post_meta($product_id, '_fetchapp_id', true);

						// If there's a fetch SKU set, we need to push this order up
						if($fetch_sku):
					    	$order_item = new FetchApp\API\OrderItem();
							$order_item->setSKU($fetch_sku);
							$items[] = $order_item;
						endif;
					endforeach;

					/* Push to Fetch */
				    $response = $order->create($items);

				    if($this->debug):
					    var_dump($response);
					endif;
				}
				catch (Exception $e){
					if($this->debug):
					    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
					    echo $e->getMessage();
					endif;
				}

			}

			public function getWCOrderItems($order){
				global $wpdb;

				$order_item_table = $wpdb->prefix . 'woocommerce_order_items';
				$order_item_meta_table = $wpdb->prefix .'woocommerce_order_itemmeta';

				$order_item_results = $wpdb->get_col( $wpdb->prepare( 
									"
									SELECT      order_item_id
									FROM        {$order_item_table}
									WHERE       order_id = %s 
									",
									$order->ID
								) ); 

				$items = array();
				foreach ( $order_item_results as $item_id):
					$item_data = array('order_item_id' => $item_id);

					$order_item_meta_results = $wpdb->get_results( $wpdb->prepare( 
									"
									SELECT      meta_key, meta_value
									FROM        {$order_item_meta_table}
									WHERE       order_item_id = %s
									",
									$item_id
								) ); 

					foreach($order_item_meta_results as $data):
						$new_key = substr($data->meta_key, 1); 
						$item_data[$new_key] = $data->meta_value;
					endforeach;
					$items[] = $item_data;
				endforeach;

				return $items;
			}

			public function pushProductToFetch($wc_product){
				global $WC_FetchApp;

				try{
					/* Remap Order Fields */
					//getFiles

					$fetch_sku = get_post_meta($wc_product->id, '_fetchapp_id', true);

					$fetch_product = $WC_FetchApp->fetchApp->getProduct($fetch_sku);
					$fetch_product_id = $fetch_product->getProductID();

//					$fetch_product->setSKU($wc_product->id ); 
					$fetch_product->setPrice(get_post_meta($wc_product->id, '_regular_price', true) ); 
					$fetch_product->setName($wc_product->post->post_title ); 
			
					$files = $fetch_product->getFiles();

					/* Push to Fetch */
				    $response = $fetch_product->update($files);

				    if($this->debug):
					    var_dump($response);
					endif;
				}
				catch (Exception $e){

				    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
				    echo $e->getMessage();
				}
			}


			public function pullProductsFromFetch(){
				$products = array();
				try{
				    $products = $this->fetchApp->getProducts();
				}
				catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
				    echo $e->getMessage();
				}
				return $products;
			}

			public function pullOrdersFromFetch(){
				$orders = array();
				try{
				    $orders = $this->fetchApp->getOrders();
				}
				catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
				    echo $e->getMessage();
				}
				return $orders;
			}


			public function insertFetchOrder($fetch_order){
				global $woocommerce;

				$wp_order = $this->mapFetchOrderToWC($fetch_order);

				//var_dump("Inserting Order");
				//var_dump($wp_order);

				$error = false;

				

				//	var_dump(wp_insert_post($wp_order, $error) );
				//	var_dump($error);
			}

			public function insertFetchProduct($fetch_product){
				global $woocommerce;

				$wp_product = $this->mapFetchProductToWC($fetch_product);

				//var_dump("Inserting Order");
				//var_dump($wp_product);

				$error = false;

				//	var_dump(wp_insert_post($wp_product, $error) );
				//	var_dump($error);
			}

			/* WC Specific */
			public function makeSettingsScreen(){
				add_action( 'woocommerce_settings_general_options_after', array( $this, 'admin_settings' ), 21);
				add_action( 'woocommerce_update_options_general', array( $this, 'save_admin_settings' ) );
		//		add_action( 'admin_enqueue_scripts', array( &$this, 'wc_fetchapp_admin_scripts' ) );

				// Frontend
				add_action( 'init', array( $this, 'wc_fetchapp_fire_customisations' ) );
				add_filter( 'body_class', array( $this, 'wc_fetchapp' ) );
			}





			

			public function mapFetchOrderToWC($fetch_order){
				global $wpdb, $WC_FetchApp;

				/* Map Fields */

			//	var_dump($wc_order);
			//	var_dump($fetch_order);
				//Order &ndash; Aug 03, 2013 @ 02:27 PM

				/* Insert / Update Order Post */
				$new_post = array(
									'post_title' => 'Order &ndash; '.$order_date,
									'post_content' => '',
									'post_status' => 'publish',
									'post_type' => 'shop_order'
								);

				$post_id = wp_insert_post($new_post);

			//		    	$order_item = new FetchApp\API\OrderItem();
			//				$order_item->setSKU($fetch_sku);
			//				$items[] = $order_item;

				/* Insert / Update Order Post Meta */
				update_post_meta($post_id, '_billing_first_name', (string)$fetch_order->getFirstName() );
				update_post_meta( $post_id, '_billing_last_name', (string)$fetch_order->getLastName()  );
				update_post_meta( $post_id, '_billing_email', (string)$fetch_order->getEmailAddress()  );
				update_post_meta( $post_id, '_fetchapp_id', (string)$fetch_order->getOrderID()  );
				update_post_meta( $post_id, '_order_total', (float)$fetch_order->getTotal()  );



				/* Insert / Update Order Items */
				$fetch_items = $fetch_order->getItems();

				$order_item_table = $wpdb->prefix . 'woocommerce_order_items';
				$order_item_meta_table = $wpdb->prefix .'woocommerce_order_itemmeta';


				foreach($fetch_items as $fetch_item):
					$fetch_product_id = $fetch_item->getSKU();
					$fetch_product = $WC_FetchApp->fetchApp->getProduct($fetch_product_id);

					$wc_product = $this->getWCProductByFetchSKU($fetch_product_id);


					$order_item = array(
										'order_id' => $post_id,
										'order_item_name' => $fetch_product->getName(),
										'order_item_type' => 'line_item'
									);

					$wpdb->insert($order_item_table, $order_item);
					$order_item_id = $wpdb->insert_id;

					/* Insert / Update Order Item Meta */
					$order_item_meta = array(
											'order_item_id' => $order_item_id,
											'meta_key' => '_qty',
											'meta_value' => 1
										);

					$wpdb->insert($order_item_meta_table, $order_item_meta);

					$order_item_meta = array(
											'order_item_id' => $order_item_id,
											'meta_key' => '_product_id',
											'meta_value' => $wc_product->id
										);

					$wpdb->insert($order_item_meta_table, $order_item_meta);

					$order_item_meta = array(
											'order_item_id' => $order_item_id,
											'meta_key' => '_line_subtotal',
											'meta_value' => $fetch_item->getPrice()
										);

					$wpdb->insert($order_item_meta_table, $order_item_meta);

					$order_item_meta = array(
											'order_item_id' => $order_item_id,
											'meta_key' => '_line_total',
											'meta_value' => $fetch_item->getPrice()
										);

					$wpdb->insert($order_item_meta_table, $order_item_meta);
				endforeach;

				return true;
			}

			public function getWCProductByFetchSKU($sku){
				global $wpdb;

				$post_meta = $wpdb->get_row( $wpdb->prepare( 
									"
									SELECT      post_id
									FROM        $wpdb->postmeta
									WHERE       meta_key = '_fetchapp_id' AND meta_value = %s 
									",
									$sku
								) ); 

				$wc_product_id = $post_meta->post_id;

				$product_factory = new WC_Product_Factory();
				$product = $product_factory->get_product($wc_product_id);
				return $product;
			}

			/* Need Product API */
			public function mapWCProductToFetch($wc_product){
				/* Map Fields */
				$fetch_product = $wc_product;
				return $fetch_product;
			}

			/* Need Product API */
			public function mapFetchProductToWC($fetch_product){
				global $woocommerce;

				//echo "test123";
				//var_dump($fetch_product);

			//	$wc_product = new WC_Product_Simple();

				$new_post = array(
				'post_title' => $fetch_product->getName(),
				'post_content' => 'Lorem ipsum dolor sit amet...',
				'post_status' => 'publish',
				'post_type' => 'product'
				);

				$post_id = wp_insert_post($new_post);

				update_post_meta($post_id, '_sku', (string)$fetch_product->getSKU() );
				update_post_meta( $post_id, '_price', (float)$fetch_product->getPrice()  );
				update_post_meta( $post_id, '_regular_price', (float)$fetch_product->getPrice()  );
				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_fetchapp_id', (string)$fetch_product->getSKU() );
				update_post_meta( $post_id, 'fetchapp_id', (string)$fetch_product->getSKU() );
				update_post_meta( $post_id, '_visibility', 'visible');

				update_post_meta( $post_id, '_sold_individually', 'yes');


				/* Map Fields */
				//var_dump($wc_product);
				//echo "Hey";


				return $wc_product;
			}


			public function getWCProducts(){
				$products = get_posts( array(
		           'post_type'      => array( 'product'),
		           'posts_per_page' => -1,
		           'post_status'    => 'publish',
		          /* 'meta_query'     => array(
		               array(
		                   'key'        => '_sale_price',
		                   'value'      => 0,
		                   'compare'    => '>=',
		                   'type'       => 'DECIMAL',
		               ),
		               array(
		                   'key'        => '_sale_price',
		                   'value'      => '',
		                   'compare'    => '!=',
		                   'type'       => '',
		               )
		          ),*/
		           'fields'         => 'id=>parent',
		       ) );
   

				$out = array();
				foreach(array_keys($products) as $product):
					$out[] = get_post($product);
				endforeach;

				return $out;
			}

			public function getWCOrders(){
				$orders = get_posts( array(
		           'post_type'      => array( 'shop_order'),
		           'posts_per_page' => -1,
		           'post_status'    => 'publish',
		          /* 'meta_query'     => array(
		               array(
		                   'key'        => '_sale_price',
		                   'value'      => 0,
		                   'compare'    => '>=',
		                   'type'       => 'DECIMAL',
		               ),
		               array(
		                   'key'        => '_sale_price',
		                   'value'      => '',
		                   'compare'    => '!=',
		                   'type'       => '',
		               )
		          ),*/
		           'fields'         => 'id=>parent',
		       ) );
   

		    $out = array();
			foreach(array_keys($orders) as $order_id):
				$out[] = get_post($order_id);
			endforeach;

		       return $out;
			}
			/* End WC Specific */

			/* ToDo */
			public function setScheduledSync(){


			}

			/* Add Fields */
			/* Settings */




			/*-----------------------------------------------------------------------------------*/
			/* Class Functions */
			/*-----------------------------------------------------------------------------------*/

			// Load the settings
			function admin_settings() {
				woocommerce_admin_fields( $this->settings );
			}



			// Save the settings
			function save_admin_settings() {				
				woocommerce_update_options( $this->settings );

				/* check if account works, set message */
				try{
					/* Remap Order Fields */
					$account = $this->fetchApp->getAccountDetails();
				   

					/* Push to Fetch */

				    if($this->debug):
					    var_dump($account);
					endif;
				}
				catch (Exception $e){

				    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
				    echo $e->getMessage();
				}
			}

			// Admin scripts
			function wc_fetchapp_admin_scripts() {
				$screen       = get_current_screen();
			    $wc_screen_id = strtolower( __( 'WooCommerce', 'woocommerce' ) );

			    // WooCommerce admin pages
			    if ( in_array( $screen->id, apply_filters( 'woocommerce_screen_ids', array( 'toplevel_page_' . $wc_screen_id, $wc_screen_id . '_page_woocommerce_settings' ) ) ) ) {
			    	//echo 'settings';
			    //	wp_enqueue_script( 'wc-pdc-script', plugins_url( '/assets/js/script.min.js', __FILE__ ) );

			    }
			}

			// Setup styles
			function wc_fetchapp_styles() {
				//wp_enqueue_style( 'pdc-layout-styles', plugins_url( '/assets/css/layout.css', __FILE__ ) );
			}

			// Fire customisations!
			function wc_fetchapp_fire_customisations() {

				
			}
		}


	endif;




//endif;

function fetchapp_wc_checkout($order_id){
	$WC_FetchApp = new WC_FetchApp();


	$wc_order = new WC_Order();
	$wc_order->get_order($order_id);


	foreach($wc_order->get_items() as $item):
		$product_id = $item['product_id'];
		$product_factory = new WC_Product_Factory();
		$product = $product_factory->get_product($product_id);
		$fetch_sku = get_post_meta($product_id, '_fetchapp_id');

		// If there's a fetch SKU set, we need to push this order up
		if($fetch_sku):
			$WC_FetchApp->pushOrderToFetch($wc_order);
		endif;
	endforeach;

	exit;
}

function fetchapp_save_post($post_id, $post){
	global $WC_FetchApp;


	if($post->post_type == 'product'):
		$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);

		if($fetch_sku):
			$product_factory = new WC_Product_Factory();
			$wc_product = $product_factory->get_product($post_id);
			$WC_FetchApp->pushProductToFetch($wc_product);



		endif;
	endif;
}


function test123(){
	global $WC_FetchApp;
	//echo "test123";
	$WC_FetchApp = new WC_FetchApp();
	//$WC_FetchApp->syncAllOrders();

//			$WC_FetchApp->syncAllProducts();
}

	add_action('plugins_loaded','test123');
add_action('woocommerce_thankyou', 'fetchapp_wc_checkout');
add_action( 'save_post', 'fetchapp_save_post', 20, 2 );


$WC_FetchApp;




