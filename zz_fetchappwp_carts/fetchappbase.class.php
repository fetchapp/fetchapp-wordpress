<?php
/*
Plugin Name: FetchApp
Plugin URI: http://www.fetchapp.com/
Description: Fetch App Integration for WooCommerce
Author: Patrick Conant
Version: 1.0.5
Author URI: http://www.prcapps.com/
*/

$class_path = plugin_dir_path( __FILE__ );
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/APIWrapper.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/AccountDetail.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/Currency.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/Order.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/OrderItem.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/OrderStatus.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/Product.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/ProductStatistic.class.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FileDetail.class.php");

if ( ! class_exists( 'WP_FetchAppBase' ) ) :

	class WP_FetchAppBase {

		public function __construct(){
			$this->debug = false;
			$this->scheduled_sync = false;
			$this->fetchapp_send_incomplete_orders = false;
			$this->fetchapp_use_ssl = true;

			// Default options
			add_option( 'fetchapp_token', '' );
			add_option( 'fetchapp_key', '' );
			add_option( 'fetchapp_debug_mode', 0);
			add_option( 'fetchapp_scheduled_sync', 0);
			add_option( 'fetchapp_send_incomplete_orders', 0);
			add_option( 'fetchapp_use_ssl', 1);


			if ( get_option( 'fetchapp_key' ) ):
				$fetchapp_key_option = get_option( 'fetchapp_key' );
				$this->fetch_key = $fetchapp_key_option['text_string'];
			endif;

			if ( get_option( 'fetchapp_token' ) ):
				$fetchapp_token_option = get_option( 'fetchapp_token' );
				$this->fetch_token = $fetchapp_token_option['text_string'];
			endif;

			if ( get_option( 'fetchapp_debug_mode' ) ):
				$debug_option = get_option( 'fetchapp_debug_mode' );

				if(isset($debug_option)):
					$this->debug = $debug_option;
				endif;
			endif;

			if ( get_option( 'fetchapp_use_ssl' ) ):
				$fetchapp_ssl_option = get_option( 'fetchapp_use_ssl' );
				if(isset($fetchapp_ssl_option)):
					$this->fetchapp_use_ssl = $fetchapp_ssl_option;
				endif;
			endif;

			
			if ( get_option( 'fetchapp_scheduled_sync' ) ):
				$fetchapp_scheduled_sync_option = get_option( 'fetchapp_scheduled_sync' );
				if(isset($fetchapp_scheduled_sync_option)):
					$this->scheduled_sync = $fetchapp_scheduled_sync_option;
				endif;
			endif;

			if ( get_option( 'fetchapp_send_incomplete_orders' ) ):
				$fetchapp_send_incomplete_orders_option = get_option( 'fetchapp_send_incomplete_orders' );
				$this->fetchapp_send_incomplete_orders = $fetchapp_send_incomplete_orders_option;
			endif;

			// var_dump("Debug: ".($this->debug));
			// var_dump("Sync: ".($this->scheduled_sync));
			// var_dump("Inc: ".($this->fetchapp_send_incomplete_orders));


			$this->fetchApp = new FetchApp\API\FetchApp();

			$this->fetchApp->setAuthenticationKey($this->fetch_key);
			$this->fetchApp->setAuthenticationToken($this->fetch_token);

			$this->fetchApp->setSSLMode($this->fetchapp_use_ssl);

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

			/* Admin Menu and functions */
			add_action('admin_menu', array($this, 'register_fetchapp_menu_page') );
			add_action('admin_init', array($this, 'fetchapp_admin_init') );
		}

		public function fetchapp_add_custom_box(){
			return "No cart selected.";
		}

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
				$order_id = $order->ID;

				if(! $this->fetchapp_send_incomplete_orders): /* If we don't send incomplete orders */
					
					$post_status_term_array = wp_get_post_terms( $order_id, 'shop_order_status'); /* Check the term relationship for order status */
					$post_status_term = array_pop($post_status_term_array);

					if($post_status_term && $post_status_term->name != 'completed'): /* If it's not completed, don't send it, so return */
						continue;
					else:
						$this->pushOrderToFetch($order, false); /* And send an email */

						/* But then set that this order is in sync */
						update_post_meta( $order_id, '_fetchapp_sync', 'yes');
					endif;
				else:
					$this->pushOrderToFetch($order, false); 

					/* But then set that this order is in sync */
					update_post_meta( $order_id, '_fetchapp_sync', 'yes');
				endif;
			endforeach;		
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

		/* Set a scheduled sync event */
		public function setScheduledSync(){
			if ( ! wp_next_scheduled( 'fetchapp_scheduled_sync' ) ) {
				wp_schedule_event( time(), 'hourly', 'fetchapp_scheduled_sync');
			}
		}

		public function doFetchAppScheduledSync(){
			if($fetchapp_scheduled_sync = get_option('fetchapp_scheduled_sync') && isset($fetchapp_scheduled_sync['text_string']) && $fetchapp_scheduled_sync['text_string'] == '1'):
				$this->syncAllProducts();
				$this->syncAllOrders();
			endif;
		}

		// Unused functions for admin scripts and styles
		public function wc_fetchapp_admin_scripts() {
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
		public function wc_fetchapp_styles() {
			return;
			//wp_enqueue_style( 'pdc-layout-styles', plugins_url( '/assets/css/layout.css', __FILE__ ) );
		}

		/* Admin Screen Functions */
		public function register_fetchapp_menu_page(){
		    add_menu_page( 'FetchApp Settings', 'FetchApp', 'manage_options', 'fetchapp_wc_settings', array($this, 'fetchapp_wc_settings_page'), plugins_url( 'fetchapp-for-woocommerce/images/logo.png' ), 58 ); 
		}

		public function fetchapp_wc_settings_page(){			
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

				if(isset($_POST['fetchapp_send_incomplete_orders']) && $_POST['fetchapp_send_incomplete_orders']):
					update_option('fetchapp_send_incomplete_orders', '1');
				else:
					update_option('fetchapp_send_incomplete_orders', '0');
				endif;

				if(isset($_POST['fetchapp_use_ssl']) && $_POST['fetchapp_use_ssl']):
					update_option('fetchapp_use_ssl', '1');
				else:
					update_option('fetchapp_use_ssl', '0');
				endif;

				$this->message = "Settings Updated";
				$this->showMessage("Settings Updated");
				// TODO: Validate Key / Token
			endif;

			if(isset($_POST['sync_orders'])):
				$this->syncAllOrders();
				$this->showMessage("Orders synchronized with FetchApp");

			endif;

			if(isset($_POST['sync_products'])):
				$this->syncAllProducts();
				$this->showMessage("Products synchronized with FetchApp");
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

		public function fetchapp_admin_init(){
			register_setting( 'fetchapp_key', 'fetchapp_key', array($this, 'fetchapp_key_validate') );
			register_setting( 'fetchapp_token', 'fetchapp_token', array($this, 'fetchapp_token_validate') );
			register_setting( 'fetchapp_debug_mode', 'fetchapp_debug_mode', array($this, 'fetchapp_debug_validate') );
			register_setting( 'fetchapp_scheduled_sync', 'fetchapp_scheduled_sync', array($this, 'fetchapp_debug_validate') );
			register_setting( 'fetchapp_send_incomplete_orders', 'fetchapp_send_incomplete_orders', array($this, 'fetchapp_debug_validate') );
			register_setting( 'fetchapp_use_ssl', 'fetchapp_use_ssl', array($this, 'fetchapp_use_ssl_validate') );


			add_settings_section('fetchapp_authentication', 'Authentication', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_key', 'FetchApp API Key', array($this, 'fetchapp_key_string'), 'fetchapp_wc_settings', 'fetchapp_authentication');
			add_settings_field('fetchapp_token', 'FetchApp API Token', array($this, 'fetchapp_token_string'), 'fetchapp_wc_settings', 'fetchapp_authentication');

			add_settings_section('fetchapp_debug', 'Debug', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_debug_mode', 'Show Debug Messages', array($this, 'fetchapp_debug_string'), 'fetchapp_wc_settings', 'fetchapp_debug');

			add_settings_section('fetchapp_scheduled_sync_section', 'Scheduled Sync', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_scheduled_sync', 'Sync with FetchApp every hour', array($this, 'fetchapp_scheduled_sync_string'), 'fetchapp_wc_settings', 'fetchapp_scheduled_sync_section');

			add_settings_section('fetchapp_send_incomplete_orders_section', 'Order Status', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_send_incomplete_orders', 'Push incomplete orders to FetchApp', array($this, 'fetchapp_send_incomplete_orders_string'), 'fetchapp_wc_settings', 'fetchapp_send_incomplete_orders_section');

			add_settings_section('fetchapp_use_ssl_header', 'SSL', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_use_ssl', 'Use SSL to connect to FetchApp', array($this, 'fetchapp_use_ssl_string'), 'fetchapp_wc_settings', 'fetchapp_use_ssl_header');

		}
		
		public function plugin_section_text() {
			echo '';
		}

		public function fetchapp_token_validate($input){
			$options = get_option('fetchapp_token');
			$options['text_string'] = trim($input['text_string']);
			return $options;
		}

		public function fetchapp_key_validate($input){
			$options = get_option('fetchapp_key');
			$options['text_string'] = trim($input['text_string']);
			return $options;
		}

		public function fetchapp_debug_validate($input){
			$options = get_option('fetchapp_debug_mode');

			$options = trim($input);
			return $options;
		}

		public function fetchapp_use_ssl_validate($input){
			$options = get_option('fetchall_use_ssl');

			$options = trim($input);
			return $options;
		}

		public function fetchapp_key_string() {
			$options = get_option('fetchapp_key');
			echo "<input id='fetchapp_key_string' name='fetchapp_key[text_string]' size='40' type='text' value='{$options['text_string']}' />";
		}


		public function fetchapp_token_string() {
			$options = get_option('fetchapp_token');
			echo "<input id='fetchapp_token' name='fetchapp_token[text_string]' size='40' type='text' value='{$options['text_string']}' />";
		}

		public function fetchapp_debug_string() {
			$options = get_option('fetchapp_debug_mode');
			echo "<input id='fetchapp_debug_mode' name='fetchapp_debug_mode[text_string]' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_use_ssl_string() {
			$options = get_option('fetchapp_use_ssl');
			echo "<input id='fetchapp_use_ssl' name='fetchapp_use_ssl[text_string]' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_scheduled_sync_string() {
			$options = get_option('fetchapp_scheduled_sync');
			echo "<input id='fetchapp_scheduled_sync' name='fetchapp_scheduled_sync[text_string]' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_send_incomplete_orders_string() {
			$options = get_option('fetchapp_send_incomplete_orders');
			echo "<input id='fetchapp_send_incomplete_orders' name='fetchapp_send_incomplete_orders[text_string]' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		/* Prints the box content */
		public function fetchapp_custom_box_html($post)
		{
		    // Use nonce for verification
		    wp_nonce_field( 'fetchapp_fetchapp_field_nonce', 'fetchapp_noncename' );

		    // Get saved value, if none exists, "default" is selected
		    $saved = get_post_meta( $post->ID, '_fetchapp_sync', true);

		    if( !$saved )
		        $saved = 'yes';

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
		 		if($post->post_type == 'product'):
			 		echo "<br /><label>FetchApp SKU:</label> <strong>{$fetchapp_id}</strong>";
		 		else:
		 			echo "<br /><label>FetchApp Order ID:</label> <strong>{$fetchapp_id}</strong>";
		 		endif;
		 	endif;
		}
		/* End Admin Screen Functions */
	}
endif;