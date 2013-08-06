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

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :

	if ( ! class_exists( 'WC_FetchApp' ) ) :

		class WC_FetchApp {
			public function __construct() {
				$this->debug = true;

				// Default options
				add_option( 'fetchapp_token', '' );
				add_option( 'fetchapp_token', '' );
				add_option( 'fetchapp_debug_mode', 0);


				if ( get_option( 'fetchapp_token' ) ):
					$this->fetch_key  = get_option( 'fetchapp_key' )['text_string'];
				endif;

				if ( get_option( 'fetchapp_token' ) ):
					$this->fetch_token  = get_option( 'fetchapp_token' )['text_string'];
				endif;

				if ( get_option( 'fetchapp_debug_mode' ) ):
					$this->debug  = get_option( 'fetchapp_debug_mode' )['text_string'];
				endif;

				$this->fetchApp = new FetchApp\API\FetchApp();

				$this->fetchApp->setAuthenticationKey($this->fetch_key);
				$this->fetchApp->setAuthenticationToken($this->fetch_token);

				$this->message = false;
				$this->error = false;

				$this->init_hooks();
			}

			function showMessage($message, $errormsg = false){
				if ($errormsg):
					echo '<div id="message" class="error">';
				else:
					echo '<div id="message" class="updated fade">';
				endif;

				echo "<p><strong>$message</strong></p></div>";
			}  

			function showAdminMessages(){
				if($this->message):
					if (user_can('manage_options') ):
						$this->showMessage($this->message, $this->error);
					endif;
				endif;
			}

			public function init_hooks(){
				add_action('admin_notices', array($this, 'showAdminMessages') );    
				$this->setScheduledSync();
//				add_action( 'wp', array($this, 'setScheduledSync') );
				add_action( 'fetchapp_scheduled_sync', array($this, 'doFetchAppScheduledSync') ); 
			}

			/* Woocommerce FetchApp Actions */
			public function syncAllProducts(){
				$fetch_products = $this->pullProductsFromFetch(); 

				foreach($fetch_products as $product):
					$this->insertFetchProduct($product);
				endforeach;


				$wc_products = $this->getWCProducts(); /* Get WC Products */

				foreach($wc_products as $product):
					/* Push to Fetch */
					$this->pushProductToFetch($product);
				endforeach;				
			}

			public function syncAllOrders(){
				$fetch_orders = $this->pullOrdersFromFetch(); /* Need Product API Code */

				foreach($fetch_orders as $order):
					$this->insertFetchOrder($order);
				endforeach;


				$wc_orders = $this->getWCOrders(); /* Get WC Orders */

				foreach($wc_orders as $order):
					/* Push to Fetch */
					$this->pushOrderToFetch($order);
				endforeach;

				
			}

			

			public function pushOrderToFetch($wc_order, $send_email=false){
				global $WC_FetchApp;

				try{
					/* Remap Order Fields */
					$order = new FetchApp\API\Order();

				    $wc_order->order_custom_fields = get_post_meta( $wc_order->ID );

				    if(isset($wc_order->ID) && $wc_order->ID):
						$wc_order_id = $wc_order->ID;
					else:
						$wc_order_id = $wc_order->id;
					endif;

				    $fetch_order_id = "wc_".$wc_order_id;

				    $order->setOrderID("wc_".$wc_order_id);
				    $order->setFirstName($wc_order->order_custom_fields['_billing_first_name'][0]);
				    $order->setLastName($wc_order->order_custom_fields['_billing_last_name'][0]);
				    $order->setEmailAddress($wc_order->order_custom_fields['_billing_email'][0]);

				    $order->setVendorID($fetch_order_id);
				    // ToDO: need to set currency variable from store settings? 
//				    $order->setCurrency(FetchApp\API\Currency::USD);
				    $items = array();

				    $order_items = $this->getWCOrderItems($wc_order);

			    	foreach($order_items as $item):
						$product_id = $item['product_id'];
						$qty = $item['qty'];
						$price = $item['line_total'];

						$product_factory = new WC_Product_Factory();
						$product = $product_factory->get_product($product_id);

						$fetch_sku = get_post_meta($product_id, '_fetchapp_id', true);
						$fetch_product_sync = get_post_meta($product_id, '_fetchapp_sync', true);


						// If there's a fetch SKU set, we need to push this order up
						if($fetch_sku && $fetch_product_sync == 'yes'):
					    	$order_item = new FetchApp\API\OrderItem();
							$order_item->setSKU($fetch_sku);
							$order_item->setProductName($product->post->post_title);
							$order_item->setOrderID($fetch_order_id);

							if($price):
								$order_item->setPrice((float)$price);
							endif;
						
							$items[] = $order_item;
						endif;
					endforeach;

					$fetch_saved_order_id = get_post_meta($wc_order_id, '_fetchapp_id', true);

					$fetch_order = $WC_FetchApp->fetchApp->getOrder($fetch_saved_order_id);

					if(! $fetch_order->getOrderID() ):
					    $response = $order->create($items, $send_email);

						update_post_meta( $wc_order_id, '_fetchapp_id', $fetch_order_id );
					else:
						$order->setOrderID($fetch_order->getOrderID() );
					    $response = $order->update($items, $send_email);
						//update_post_meta( $wc_product_id, '_fetchapp_id', $fetch_saved_order_id );
					endif;

					/* Push to Fetch */

				    if($this->debug):
		    			$this->showMessage("FetchApp Debug:".print_r($response, true) );
					endif;
				}
				catch (Exception $e){
					if($this->debug):
					    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
		    			$this->showMessage("FetchApp Debug:".$e->getMessage() );
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
					if(isset($wc_product->ID) && $wc_product->ID):
						$wc_product_id = $wc_product->ID;
						$wc_product_title = $wc_product->post_title;
					else:
						$wc_product_id = $wc_product->id;
						$wc_product_title = $wc_product->post->post_title;
					endif;

					$wc_sku = get_post_meta($wc_product_id, '_sku', true);

					if(! $wc_sku):
						$wc_sku = 'wc_'.$wc_product_id; 
					endif;

					/* Validation for create must include price, and name. SKU can be generated from product ID */

					$fetch_sku = get_post_meta($wc_product_id, '_fetchapp_id', true);

					$fetch_product = $WC_FetchApp->fetchApp->getProduct($fetch_sku);

					if($fetch_product && ! $fetch_product->getProductID() ):

						$fetch_product = new FetchApp\API\Product();

						$fetch_sku = $wc_sku;
						$fetch_product->setSKU($fetch_sku); 
						$fetch_product->setProductID($fetch_sku);
						$fetch_product->setPrice(get_post_meta($wc_product_id, '_regular_price', true) ); 
						$fetch_product->setName($wc_product_title ); 

						update_post_meta( $wc_product_id, '_fetchapp_id', $fetch_sku );

					    $response = $fetch_product->create(array() );
					else:
						$fetch_sku = $wc_sku;
						$fetch_product->setSKU($fetch_sku); 

						$fetch_product->setPrice(get_post_meta($wc_product_id, '_regular_price', true) ); 
						$fetch_product->setName($wc_product_title ); 
				
						update_post_meta( $wc_product_id, '_fetchapp_id', $fetch_sku );

						$files = $fetch_product->getFiles();

						/* Push to Fetch */
					    $response = $fetch_product->update($files);
					endif;

				    if($this->debug):
		    			$this->showMessage("FetchApp Debug:".print_r($response, true) );
					endif;
				}
				catch (Exception $e){

				    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
	    			$this->showMessage("FetchApp Debug:".$e->getMessage() );
				}
			}


			public function pullProductsFromFetch(){
				$products = array();
				try{
				    $products = $this->fetchApp->getProducts();
				}
				catch (Exception $e){
					// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
	    			$this->showMessage("FetchApp Debug:".$e->getMessage() );
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
	    			$this->showMessage("FetchApp Debug:".$e->getMessage() );
				}
				return $orders;
			}


			public function insertFetchOrder($fetch_order){
				global $wpdb, $WC_FetchApp;

				/* Insert / Update Order Post */
				$fetch_order_id = $fetch_order->getOrderID();

				$existing_wc_order = $this->getWCOrderByFetchSKU($fetch_order_id);

				$post_id = false;
				
				/* ToDo: Set Order Date */
				$order_date = "";

				$post_id;
				if($existing_wc_order):
					if($this->debug):
		    			$WC_FetchApp->showMessage("FetchApp Debug:".print_r($fetch_order->getFirstName(), true) );
		    			$WC_FetchApp->showMessage("FetchApp Debug:".print_r($existing_wc_order, true) );
		    			$WC_FetchApp->showMessage("FetchApp Debug:".print_r("Updating order: {$existing_wc_order->ID}", true) );
					endif;

					$post_id = $existing_wc_order->ID;

					$updated_post = array(
						'post_title' => 'Order &ndash; '.$order_date,
						'post_content' => '',
						'post_status' => 'publish',
						'post_type' => 'shop_order',
						'ID' => $post_id
						);

					wp_update_post($updated_post);
				else:
					$new_post = array(
									'post_title' => 'Order &ndash; '.$order_date,
									'post_content' => '',
									'post_status' => 'publish',
									'post_type' => 'shop_order'
								);

					$post_id = wp_insert_post($new_post);

					if($this->debug):
		    			$WC_FetchApp->showMessage("FetchApp Debug:".print_r("Created order: {$post_id}", true) );
					endif;
					
				endif;



				

			//		    	$order_item = new FetchApp\API\OrderItem();
			//				$order_item->setSKU($fetch_sku);
			//				$items[] = $order_item;

				/* Insert / Update Order Post Meta */
				update_post_meta($post_id, '_billing_first_name', (string)$fetch_order->getFirstName() );
				update_post_meta( $post_id, '_billing_last_name', (string)$fetch_order->getLastName()  );
				update_post_meta( $post_id, '_billing_email', (string)$fetch_order->getEmailAddress()  );
				update_post_meta( $post_id, '_fetchapp_id', (string)$fetch_order->getOrderID()  );
				update_post_meta( $post_id, '_fetchapp_sync', 'yes');
				update_post_meta( $post_id, '_order_total', (float)$fetch_order->getTotal()  );



				/* Insert / Update Order Items */
				$fetch_items = $fetch_order->getItems();

				$order_item_table = $wpdb->prefix . 'woocommerce_order_items';
				$order_item_meta_table = $wpdb->prefix .'woocommerce_order_itemmeta';

				/* Delete Existing Order Items */
				// Get Current Order Items
				// Delete their meta
				// Delete them
				 $querystr = "
					    SELECT $wpdb->posts.* 
					    FROM $wpdb->posts, $wpdb->postmeta
					    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
					    AND $wpdb->postmeta.meta_key = 'tag' 
					    AND $wpdb->postmeta.meta_value = 'email' 
					    AND $wpdb->posts.post_status = 'publish' 
					    AND $wpdb->posts.post_type = 'post'
					    AND $wpdb->posts.post_date < NOW()
					    ORDER BY $wpdb->posts.post_date DESC
					 ";

					// $pageposts = $wpdb->get_results($querystr, OBJECT);

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

			public function insertFetchProduct($fetch_product){
				global $woocommerce;

				$fetch_product_sku = $fetch_product->getSKU();

				$existing_wc_product = $this->getWCProductByFetchSKU($fetch_product_sku);

				$post_id = false;
				if($existing_wc_product):
					$post_id = $existing_wc_product->id;

					$updated_post = array(
						'post_title' => $fetch_product->getName(),
						'post_content' => '',
						'post_status' => 'publish',
						'post_type' => 'product',
						'ID' => $post_id
						);

					wp_update_post($updated_post);
				else:
					$new_post = array(
					'post_title' => $fetch_product->getName(),
					'post_content' => '',
					'post_status' => 'publish',
					'post_type' => 'product'
					);

					$post_id = wp_insert_post($new_post);
				endif;

				update_post_meta( $post_id, '_sku', (string)$fetch_product->getSKU() );
				update_post_meta( $post_id, '_price', (float)$fetch_product->getPrice()  );
				update_post_meta( $post_id, '_regular_price', (float)$fetch_product->getPrice()  );
				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_fetchapp_id', (string)$fetch_product->getSKU() );
				update_post_meta( $post_id, '_fetchapp_sync', 'yes' );
				update_post_meta( $post_id, '_visibility', 'visible');
				update_post_meta( $post_id, '_sold_individually', 'yes');

				return $wc_product;
			}

			/* WC Specific */
			public function getWCOrderByFetchSKU($sku){
				global $wpdb;

				$post_meta = $wpdb->get_row( $wpdb->prepare( 
									"
									SELECT      post_id
									FROM        $wpdb->postmeta
									WHERE       meta_key = '_fetchapp_id' AND meta_value = %s 
									",
									$sku
								) ); 

				if($post_meta):
					$wc_order_id = $post_meta->post_id;
					$wc_order = get_post($wc_order_id);
					return $wc_order;
				else:
					return false;
				endif;
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

				if($post_meta):
					$wc_product_id = $post_meta->post_id;

					$product_factory = new WC_Product_Factory();
					$product = $product_factory->get_product($wc_product_id);
					return $product;
				else:
					return false;
				endif;
			}

			public function mapWCProductToFetch($wc_product){
				/* Map Fields */
				$fetch_product = $wc_product;
				return $fetch_product;
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
		           'fields'         => 'id=>parent',
		       ) );
   
			    $out = array();
				foreach(array_keys($orders) as $order_id):
					$out[] = get_post($order_id);
				endforeach;

		       return $out;
			}
			/* End WC Specific */

			/* Set a scheduled sync event */
			public function setScheduledSync(){
				if ( ! wp_next_scheduled( 'fetchapp_scheduled_sync' ) ) {
					wp_schedule_event( time(), 'hourly', 'fetchapp_scheduled_sync');
				}
			}

			public function doFetchAppScheduledSync(){
				if(get_option('fetchapp_scheduled_sync') && isset(get_option('fetchapp_scheduled_sync')['text_string']) && get_option('fetchapp_scheduled_sync')['text_string'] == '1'):
					$this->syncAllProducts();
					$this->syncAllOrders();
				endif;
			}

			// Unused functions for admin scripts and styles
			function wc_fetchapp_admin_scripts() {
				return;
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
				return;
				//wp_enqueue_style( 'pdc-layout-styles', plugins_url( '/assets/css/layout.css', __FILE__ ) );
			}
		}
	endif;
endif;

function fetchapp_wc_checkout($order_id){
	$WC_FetchApp = new WC_FetchApp();

	$wc_order = new WC_Order();
	$wc_order->get_order($order_id);

	$push_to_fetch = false;
	foreach($wc_order->get_items() as $item):
		$product_id = $item['product_id'];
		$product_factory = new WC_Product_Factory();
		$product = $product_factory->get_product($product_id);
		$fetch_sku = get_post_meta($product_id, '_fetchapp_id');
		$fetch_product_sync = get_post_meta($product_id, '_fetchapp_sync');

		if(is_array($fetch_product_sync)):
			$fetch_product_sync = array_pop($fetch_product_sync);
		endif;

		// If there's a fetch SKU set, we need to push this order up
		if($fetch_sku && $fetch_product_sync == 'yes'):
			$push_to_fetch = true; 
			break;
		endif;
	endforeach;

	if($push_to_fetch):
		$wc_order_post = get_post($order_id);

		/* Intentionally Ignore the Sync to Fetch Here */
		$WC_FetchApp->pushOrderToFetch($wc_order_post, true); /* And send an email */

		/* But then set that this order is in sync */
		update_post_meta( $order_id, '_fetchapp_sync', 'yes');
	endif;
}

function fetchapp_save_post($post_id, $post){
	global $WC_FetchApp;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ):
		return;
	endif;

	if($post->post_type == 'product' || $post->post_type == 'shop_order'):
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['fetchapp_noncename'], 'fetchapp_fetchapp_field_nonce' ) ):
			return;
		endif;

		if ( isset($_POST['_fetchapp_sync']) && $_POST['_fetchapp_sync'] != "" ):
			update_post_meta( $post_id, '_fetchapp_sync', $_POST['_fetchapp_sync'] );
		else:
			update_post_meta( $post_id, '_fetchapp_sync', 'no' );
		endif;


		$sync_with_fetch = get_post_meta( $post_id, '_fetchapp_sync', true);

		if($sync_with_fetch != 'yes'):
			return;
		endif;
	endif;

	$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);

	if($post->post_type == 'product'):
		if($fetch_sku):
			$wc_product = $WC_FetchApp->getWCProductByFetchSKU($fetch_sku);
			$WC_FetchApp->pushProductToFetch($wc_product);
		else:
			// Create new product in FetchApp
			$WC_FetchApp->pushProductToFetch($post);			
		endif;
	elseif($post->post_type == 'shop_order'):
		if($fetch_sku):
			$wc_order = $WC_FetchApp->getWCOrderByFetchSKU($fetch_sku);
			$WC_FetchApp->pushOrderToFetch($wc_order);
		else:
			// Create new order in FetchApp
			$WC_FetchApp->pushOrderToFetch($post);			
		endif;
	endif;
}

function fetchapp_delete_post($post_id){
	global $WC_FetchApp;
	$post = get_post($post_id);

	if($post->post_type == 'product'):
		$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);
		if($fetch_sku):
			try{
				$fetch_product = $WC_FetchApp->fetchApp->getProduct($fetch_sku);

				if($fetch_product->getProductID() ):
					$fetch_product->delete();
				endif;
			}
			catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$WC_FetchApp->showMessage("FetchApp Debug:".$e->getMessage() );
			}				
		endif;
	elseif($post->post_type == 'shop_order'):
		$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);
		if($fetch_sku):
			try{
				$fetch_order = $WC_FetchApp->fetchApp->getOrder($fetch_sku);

				if($fetch_order->getOrderID() ):
					$fetch_order->delete();
				endif;
			}
			catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$WC_FetchApp->showMessage("FetchApp Debug:".$e->getMessage() );
			}				
		endif;
	endif;
}

function fetchapp_init(){
	global $WC_FetchApp;

	$WC_FetchApp = new WC_FetchApp();


}

function register_fetchapp_menu_page(){
    add_menu_page( 'FetchApp Settings', 'FetchApp', 'manage_options', 'fetchapp_wc_settings', 'fetchapp_wc_settings_page', plugins_url( 'fetchapp/images/logo.png' ), 60 ); 
}

function fetchapp_wc_settings_page(){
	global $WC_FetchApp;
	
	if(isset($_POST['update_fetchapp_settings'])):
		
		$possible_settings = array('fetchapp_key', 'fetchapp_token');

		foreach($possible_settings as $key):
			if(isset($_POST[$key])):
				update_option($key, $_POST[$key]);
			endif;
		endforeach;
		
		if(isset($_POST['fetchapp_debug_mode']) && $_POST['fetchapp_debug_mode']):
			update_option('fetchapp_debug_mode', '1');
		else:
			update_option('fetchapp_debug_mode', '0');
		endif;

		if(isset($_POST['fetchapp_scheduled_sync']) && $_POST['fetchapp_scheduled_sync']):
			update_option('fetchapp_scheduled_sync', '1');
		else:
			update_option('fetchapp_scheduled_sync', '0');
		endif;

		$WC_FetchApp->message = "Settings Updated";
		$WC_FetchApp->showMessage("Settings Updated");
		// TODO: Validate Key / Token
	endif;

	if(isset($_POST['sync_orders'])):
		$WC_FetchApp->syncAllOrders();
		$WC_FetchApp->showMessage("Orders synchronized with FetchApp");

	endif;

	if(isset($_POST['sync_products'])):
		$WC_FetchApp->syncAllProducts();
		$WC_FetchApp->showMessage("Products synchronized with FetchApp");
	endif;

	echo "<div>
	<h2>FetchApp Settings</h2>
	<form method=\"post\">
	";
	echo  settings_fields('plugin_options').do_settings_sections('fetchapp_wc_settings');
	echo "<br /><br /><input name=\"update_fetchapp_settings\" type=\"submit\" value=\"Save Settings\" /><br /><br />";

	echo "<h3>Sync Actions</h3>";
	echo "<input name=\"sync_orders\" type=\"submit\" value=\"Sync Orders\" />";
	echo "<input name=\"sync_products\" type=\"submit\" value=\"Sync Products\" />";
	echo "</form></div>";
}

function fetchapp_admin_init(){
	register_setting( 'fetchapp_key', 'fetchapp_key', 'fetchapp_key_validate' );
	register_setting( 'fetchapp_token', 'fetchapp_token', 'fetchapp_token_validate' );
	register_setting( 'fetchapp_debug_mode', 'fetchapp_debug_mode', 'fetchapp_debug_validate' );
	register_setting( 'fetchapp_scheduled_sync', 'fetchapp_scheduled_sync', 'fetchapp_debug_validate' );


	add_settings_section('fetchapp_authentication', 'Authentication', 'plugin_section_text', 'fetchapp_wc_settings');
	add_settings_field('fetchapp_key', 'FetchApp API Key', 'fetchapp_key_string', 'fetchapp_wc_settings', 'fetchapp_authentication');
	add_settings_field('fetchapp_token', 'FetchApp API Token', 'fetchapp_token_string', 'fetchapp_wc_settings', 'fetchapp_authentication');

	add_settings_section('fetchapp_debug', 'Debug', 'plugin_section_text', 'fetchapp_wc_settings');
	add_settings_field('fetchapp_debug_mode', 'Show Debug Messages', 'fetchapp_debug_string', 'fetchapp_wc_settings', 'fetchapp_debug');

	add_settings_section('fetchapp_scheduled_sync_section', 'Scheduled Sync', 'plugin_section_text', 'fetchapp_wc_settings');
	add_settings_field('fetchapp_scheduled_sync', 'Sync with FetchApp every hour', 'fetchapp_scheduled_sync_string', 'fetchapp_wc_settings', 'fetchapp_scheduled_sync_section');

}

function fetchapp_token_validate($input){
	$options = get_option('fetchapp_token');
	$options['text_string'] = trim($input['text_string']);
	return $options;
}

function fetchapp_key_validate($input){
	$options = get_option('fetchapp_key');
	$options['text_string'] = trim($input['text_string']);
	return $options;
}

function fetchapp_debug_validate($input){
	$options = get_option('fetchapp_debug_mode');
	$options['text_string'] = trim($input['text_string']);
	return $options;
}

function fetchapp_key_string() {
	$options = get_option('fetchapp_key');
	echo "<input id='fetchapp_key_string' name='fetchapp_key[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}


function fetchapp_token_string() {
	$options = get_option('fetchapp_token');
	echo "<input id='fetchapp_token' name='fetchapp_token[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

function fetchapp_debug_string() {
	$options = get_option('fetchapp_debug_mode');
	echo "<input id='fetchapp_debug_mode' name='fetchapp_debug_mode[text_string]' type='checkbox' value='1' ".checked($options['text_string'], 1, false)." />";
}

function fetchapp_scheduled_sync_string() {
	$options = get_option('fetchapp_scheduled_sync');
	echo "<input id='fetchapp_scheduled_sync' name='fetchapp_scheduled_sync[text_string]' type='checkbox' value='1' ".checked($options['text_string'], 1, false)." />";
}

/* Adds a box to the main column on the Post and Page edit screens */
function fetchapp_add_custom_box() {
    add_meta_box( 
        'fetchapp_sectionid',
        'FetchApp',
        'fetchapp_inner_custom_box',
        'product',
        'side',
        'high'
    );

    add_meta_box( 
        'fetchapp_sectionid',
        'FetchApp',
        'fetchapp_inner_custom_box',
        'shop_order',
        'side',
        'high'
    );
}

/* Prints the box content */
function fetchapp_inner_custom_box($post)
{
    // Use nonce for verification
    wp_nonce_field( 'fetchapp_fetchapp_field_nonce', 'fetchapp_noncename' );

    // Get saved value, if none exists, "default" is selected
    $saved = get_post_meta( $post->ID, '_fetchapp_sync', true);

    if( !$saved )
        $saved = 'default';

    $fields = array(
        'yes'       => __('Sync with FetchApp', 'wpse')
    );

    foreach($fields as $key => $label)
    {
        printf(
            '<input type="checkbox" name="_fetchapp_sync" value="%1$s" id="_fetchapp_sync[%1$s]" %3$s />'.
            '<label for="_fetchapp_sync[%1$s]"> %2$s ' .
            '</label><br>',
            esc_attr($key),
            esc_html($label),
            checked($saved, $key, false)
        );
    }

   $fetchapp_id = get_post_meta( $post->ID, '_fetchapp_id', true);
 	
 	if($fetchapp_id):
 		echo "<br /><label>FetchApp SKU:</label> {$fetchapp_id}";
 	endif;
}

add_action('plugins_loaded','fetchapp_init');
add_action('admin_menu', 'register_fetchapp_menu_page' );

add_action('woocommerce_thankyou', 'fetchapp_wc_checkout');
add_action( 'save_post', 'fetchapp_save_post', 20, 2 );
add_action('before_delete_post', 'fetchapp_delete_post', 20);

add_action('admin_init', 'fetchapp_admin_init');
add_action('add_meta_boxes', 'fetchapp_add_custom_box' );


$WC_FetchApp;




