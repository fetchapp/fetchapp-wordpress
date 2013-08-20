<?php
/**
 * @package FetchApp
 * @version 1.0
 */
/*
Plugin Name: Fetch App
Plugin URI: http://www.fetchapp.com/
Description: Fetch App Integration for WooCommerce
Author: Patrick Conant
Version: 1.0
Author URI: http://www.prcapps.com/
*/

$class_path = plugin_dir_path( __FILE__ );
require_once("{$class_path}/fetchappbase.class.php");

if ( ! class_exists( 'WC_FetchApp' ) ) :

	class WC_FetchApp extends WP_FetchAppBase{
		public function __construct() {
			parent::__construct();
			
		}

		/* Woocommerce FetchApp Actions */
		public function pushOrderToFetch($wc_order, $send_email=false){
			try{
				/* Remap Order Fields */
				$order = new FetchApp\API\Order();

			    $wc_order->order_custom_fields = get_post_meta( $wc_order->ID );

			    if(isset($wc_order->ID) && $wc_order->ID):
					$wc_order_id = $wc_order->ID;
				else:
					$wc_order_id = $wc_order->id;
				endif;

			    $fetch_order_id = $wc_order_id;

			    $order->setOrderID($wc_order_id);
			    $order->setFirstName($wc_order->order_custom_fields['_billing_first_name'][0]);
			    $order->setLastName($wc_order->order_custom_fields['_billing_last_name'][0]);
			    $order->setEmailAddress($wc_order->order_custom_fields['_billing_email'][0]);

			    $order->setVendorID($fetch_order_id);
			    // ToDO: The currency setting doesn't seem to take in FetchApp
			    /*$woocommerce_currency = get_option('woocommerce_currency');
				$currency_refl = new ReflectionClass('FetchApp\API\Currency');

			    $order->setCurrency($currency_refl->getConstant($woocommerce_currency) );
			    */
			    
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

				$fetch_order = $this->fetchApp->getOrder($fetch_saved_order_id);

				if(! $fetch_order->getOrderID() ):
				    if($this->debug):
				    	$this->showMessage("Creating new Order in FetchApp: ".$fetch_order_id);
				    endif;

				    $response = $order->create($items, $send_email);

					update_post_meta( $wc_order_id, '_fetchapp_id', $fetch_order_id );
				else:
					if($this->debug):
				    	$this->showMessage("Updating Order in FetchApp: ".$fetch_order->getOrderID());
				    endif;

					$order->setOrderID($fetch_order->getOrderID() );
				    $response = $order->update($items, $send_email);
					//update_post_meta( $wc_product_id, '_fetchapp_id', $fetch_saved_order_id );
				endif;

				/* Push to Fetch */

			    if($this->debug):
			    	if($response === true):
		    			$this->showMessage("FetchApp: Success");
			    	else:
		    			$this->showMessage("FetchApp: ".print_r($response, true) );
			    	endif;
				endif;
			}
			catch (Exception $e){
				if($this->debug):
				    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
	    			$this->showMessage("FetchApp: ".$e->getMessage() );
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
					$wc_sku = $wc_product_id; 
				endif;



				/* Validation for create must include price, and name. SKU can be generated from product ID */

				$fetch_sku = get_post_meta($wc_product_id, '_fetchapp_id', true);
				$fetch_product = $this->fetchApp->getProduct($fetch_sku);

				if(! $fetch_product || ! $fetch_product->getProductID() ):

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
	    			$this->showMessage("FetchApp: ".print_r($response, true) );
				endif;
			}
			catch (Exception $e){

			    // This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$this->showMessage("FetchApp: ".$e->getMessage() );
			}
		}

		public function insertFetchOrder($fetch_order){
			global $wpdb;

			/* Insert / Update Order Post */
			$fetch_order_id = $fetch_order->getOrderID();

			$existing_wc_order = $this->getWCOrderByFetchSKU($fetch_order_id);

			$post_id = false;
			
			/* ToDo: Set Order Date */
			$order_date = "";

			$post_id;
			if($existing_wc_order):
				if($this->debug):
	    		//	$this->showMessage("FetchApp: ".print_r($fetch_order->getFirstName(), true) );
	    		//	$this->showMessage("FetchApp: ".print_r($existing_wc_order, true) );
	    			$this->showMessage("FetchApp: ".print_r("Updating order in WordPress: {$existing_wc_order->ID}", true) );
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
	    			$this->showMessage("FetchApp: ".print_r("Created order: {$post_id}", true) );
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
			/* ToDO: Double Check this */

			// Get Current Order Items
			$order_item_objects = $this->getWCOrderItems($existing_wc_order);

			$order_item_array = array();
			foreach($order_item_objects as $order_item):
				$order_item_array[] = $order_item['order_item_id'];
			endforeach;

			$order_item_id_string = implode($order_item_array, ", ");

			// Delete their meta
			$delete_meta_sql = "DELETE FROM {$order_item_meta_table} WHERE order_item_id IN ({$order_item_id_string})";
			$delete_response = $wpdb->get_results($delete_meta_sql, OBJECT);

			// Delete the order items 
			$delete_sql = "DELETE FROM {$order_item_table} WHERE order_id = {$post_id}";
			$delete_response = $wpdb->get_results($delete_sql, OBJECT);

			foreach($fetch_items as $fetch_item):
				$fetch_product_id = $fetch_item->getSKU();
				$fetch_product = $this->fetchApp->getProduct($fetch_product_id);

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
										'meta_value' => (float)$fetch_item->getPrice()
									);

				$wpdb->insert($order_item_meta_table, $order_item_meta);

				$order_item_meta = array(
										'order_item_id' => $order_item_id,
										'meta_key' => '_line_total',
										'meta_value' => (float)$fetch_item->getPrice()
									);

				$wpdb->insert($order_item_meta_table, $order_item_meta);

				$order_item_meta = array(
										'order_item_id' => $order_item_id,
										'meta_key' => '_tax_class',
										'meta_value' => ''
									);

				$wpdb->insert($order_item_meta_table, $order_item_meta);

				$order_item_meta = array(
										'order_item_id' => $order_item_id,
										'meta_key' => '_variation_id',
										'meta_value' => ''
									);

				$wpdb->insert($order_item_meta_table, $order_item_meta);

				$order_item_meta = array(
										'order_item_id' => $order_item_id,
										'meta_key' => '_line_subtotal_tax',
										'meta_value' => ''
									);

				$wpdb->insert($order_item_meta_table, $order_item_meta);

				$order_item_meta = array(
										'order_item_id' => $order_item_id,
										'meta_key' => '_line_tax',
										'meta_value' => ''
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

			return get_post($post_id);
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

		/* Adds a box to the main column on the Post and Page edit screens */
		public function fetchapp_add_custom_box() {
		    add_meta_box( 
		        'fetchapp_sectionid',
		        'FetchApp',
		        array($this, 'fetchapp_custom_box_html'),
		        'product',
		        'side',
		        'high'
		    );

		    add_meta_box( 
		        'fetchapp_sectionid',
		        'FetchApp',
		        array($this, 'fetchapp_custom_box_html'),
		        'shop_order',
		        'side',
		        'high'
		    );
		}

		public function fetchapp_wc_checkout($order_id){
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
				if(! $this->fetchapp_send_incomplete_orders): /* If we don't send incomplete orders */
					
					$post_status_term_array = wp_get_post_terms( $order_id, 'shop_order_status'); /* Check the term relationship for order status */
					$post_status_term = array_pop($post_status_term_array);

					if($post_status_term && $post_status_term->name != 'completed'): /* If it's not completed, don't send it, so return */
						return;
					else:
						$this->pushOrderToFetch($wc_order_post, true); /* And send an email */

						/* But then set that this order is in sync */
						update_post_meta( $order_id, '_fetchapp_sync', 'yes');
					endif;
				endif;
			endif;
		}

		public function fetchapp_save_post($post_id, $post){

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ):
				return;
			endif;
			if($post->post_type == 'product' || $post->post_type == 'shop_order'):
				// verify this came from the our screen and with proper authorization,
				// because save_post can be triggered at other times
				//if (! isset( $_POST['fetchapp_noncename']) || !wp_verify_nonce( $_POST['fetchapp_noncename'], 'fetchapp_fetchapp_field_nonce' ) ):
				//	return;
				//endif;

				if ( isset($_POST['_fetchapp_sync']) && $_POST['_fetchapp_sync'] != "" ):
					update_post_meta( $post_id, '_fetchapp_sync', $_POST['_fetchapp_sync'] );
				else:
					// Check for existing value, if it's not set at all, don't explicitly set no (to default to YES)
					$sync_with_fetch = get_post_meta( $post_id, '_fetchapp_sync', true);

					if($sync_with_fetch == 'yes'):
						update_post_meta( $post_id, '_fetchapp_sync', 'no' );
					endif;
				endif;


				$sync_with_fetch = get_post_meta( $post_id, '_fetchapp_sync', true);

				if($sync_with_fetch != 'yes'):
					return;
				endif;
			endif;

			$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);
			if($post->post_type == 'product'):
				if($fetch_sku):
					$wc_product = $this->getWCProductByFetchSKU($fetch_sku);
				
					$this->pushProductToFetch($wc_product);
				else:
					// Create new product in FetchApp
					$this->pushProductToFetch($post);			
				endif;
			elseif($post->post_type == 'shop_order'):
				/* Check Order Status */
				if(! $this->fetchapp_send_incomplete_orders): /* If we don't send incomplete orders */
					
					$post_status_term_array = wp_get_post_terms( $post_id, 'shop_order_status'); /* Check the term relationship for order status */
					$post_status_term = array_pop($post_status_term_array);

					if($post_status_term && $post_status_term->name != 'completed'): /* If it's not completed, don't send it, so return */
						return;
					endif;
				endif;

				if($fetch_sku):
					$wc_order = $this->getWCOrderByFetchSKU($fetch_sku);
					$this->pushOrderToFetch($wc_order, true);
				else:
					// Create new order in FetchApp
					$this->pushOrderToFetch($post, true);			
				endif;
			endif;
		}

		public function fetchapp_delete_post($post_id){
			$post = get_post($post_id);

			if($post->post_type == 'product'):
				$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);
				if($fetch_sku):
					try{
						$fetch_product = $this->fetchApp->getProduct($fetch_sku);

						if($fetch_product->getProductID() ):
							$fetch_product->delete();
						endif;
					}
					catch (Exception $e){
						// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
		    			$this->showMessage("FetchApp: ".$e->getMessage() );
					}				
				endif;
			elseif($post->post_type == 'shop_order'):
				$fetch_sku = get_post_meta($post_id, '_fetchapp_id', true);
				if($fetch_sku):
					try{
						$fetch_order = $this->fetchApp->getOrder($fetch_sku);

						if($fetch_order->getOrderID() ):
							$fetch_order->delete();
						endif;
					}
					catch (Exception $e){
						// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
		    			$this->showMessage("FetchApp: ".$e->getMessage() );
					}				
				endif;
			endif;
		}
		/* End WC Specific */

		function init_hooks(){
			parent::init_hooks();

			/* These hooks are defined in this subclass */
			add_action('woocommerce_thankyou', array($this, 'fetchapp_wc_checkout') );
			add_action( 'save_post', array($this, 'fetchapp_save_post'), 20, 2 );
			add_action('before_delete_post',  array($this, 'fetchapp_delete_post'), 20);
			add_action('add_meta_boxes', array($this, 'fetchapp_add_custom_box') );
		}
	}
endif;

function fetchapp_init(){
	global $WC_FetchApp;

	$WC_FetchApp = new WC_FetchApp();
}

add_action('plugins_loaded','fetchapp_init');