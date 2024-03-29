<?php
/*
Plugin Name: FetchApp
Plugin URI: http://www.fetchapp.com/
Description: Fetch App Integration for WooCommerce
Author: Patrick Conant
Version: 1.9.2
Author URI: http://www.prcapps.com/
WC requires at least: 3.6
WC tested up to: 7.4.0
*/

$class_path = plugin_dir_path( __FILE__ );
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/FetchApp.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/APIWrapper.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/AccountDetail.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/Currency.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/Order.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/OrderItem.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/OrderStatus.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/Product.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/ProductStatistic.php");
require_once("{$class_path}/../libraries/fetchapp-php-2.0/src/FetchApp/API/FileDetail.php");

if ( ! class_exists( 'WP_FetchAppBase' ) ) :

	class WP_FetchAppBase {

		public function __construct(){
			$this->debug = false;
			$this->scheduled_sync = false;
			$this->fetchapp_send_incomplete_orders = false;
			$this->fetchapp_use_ssl = true;
			$this->pull_from_fetch_happening = false;

			// Default options
			add_option( 'fetchapp_token', '' );
			add_option( 'fetchapp_key', '' );
			add_option( 'fetchapp_debug_mode', 0);
			add_option( 'fetchapp_scheduled_sync', 0);
			add_option( 'fetchapp_send_incomplete_orders', 0);
			add_option( 'fetchapp_use_ssl', 1);
			add_option( 'fetchapp_sync_order_number', 0);


			if ( get_option( 'fetchapp_key' ) ):
				$fetchapp_key_option = get_option( 'fetchapp_key' );

				if(is_array($fetchapp_key_option) && isset($fetchapp_key_option['text_string'])):
					$fetchapp_key_option = $fetchapp_key_option['text_string'];
				endif;

				$this->fetch_key = $fetchapp_key_option;
			endif;

			if ( get_option( 'fetchapp_token' ) ):
				$fetchapp_token_option = get_option( 'fetchapp_token' );

				if(is_array($fetchapp_token_option) && isset($fetchapp_token_option['text_string'])):
					$fetchapp_token_option = $fetchapp_token_option['text_string'];
				endif;

				$this->fetch_token = $fetchapp_token_option;
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

			if ( get_option( 'fetchapp_sync_order_number' ) ):
				$fetchapp_sync_order_number_option = get_option( 'fetchapp_sync_order_number' );
				$this->fetchapp_sync_order_number = $fetchapp_sync_order_number_option;
			else:
				$this->fetchapp_sync_order_number = 0;
			endif;

			// var_dump("Debug: ".($this->debug));
			// var_dump("Sync: ".($this->scheduled_sync));
			// var_dump("Inc: ".($this->fetchapp_send_incomplete_orders));
			// var_dump("fetchapp_sync_order_number: ".($this->fetchapp_sync_order_number));


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

		public function pullAllProducts($startingPage=1, $stopPage=false){
			$this->pull_from_fetch_happening = true;
			$page = $startingPage;

			try{
				while($fetch_products = $this->pullProductsFromFetch($page, 50) ):
					if(count($fetch_products) == 0):
						break;
					endif;
					foreach($fetch_products as $product):
						$this->insertFetchProduct($product);
					endforeach;
					$page++;
					if($stopPage !== false && $page > $stopPage):
						break;
					endif;
				endwhile;
			}
			catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$this->showMessage("FetchApp: Error Pulling Products:".$e->getMessage() );
			}
			$this->pull_from_fetch_happening = false;
		}

		public function pushAllProducts($startingPage=1, $stopPage=false){
			$page = $startingPage;
			do{
				$wc_product_batch = $this->getWCProducts($page, 50);

				foreach($wc_product_batch as $product):
					/* Push to Fetch */
					$this->pushProductToFetch($product);
				endforeach;		
				$page++;
				if($stopPage !== false && $page > $stopPage):
					break;
				endif;
			}
			while(count($wc_product_batch) > 0);
		}

		public function syncAllProducts($startingPage=1, $stopPage=false){
			$this->pullAllProducts($startingPage, $stopPage);
			$this->pushAllProducts($startingPage, $stopPage);
		}

		public function pullAllOrders($startingPage=1, $stopPage=false){
			$this->pull_from_fetch_happening = true;
			$page = $startingPage;

			try{
				while($fetch_orders = $this->pullOrdersFromFetch($page, 50) ):
					if(count($fetch_orders) == 0):
						break;
					endif;
					foreach($fetch_orders as $order):
						$this->insertFetchOrder($order);
					endforeach;
					$page++;
					if($stopPage !== false && $page > $stopPage):
						break;
					endif;
				endwhile;
			}
			catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$this->showMessage("FetchApp: Error Pulling Orders:".$e->getMessage() );
			}
			$this->pull_from_fetch_happening = false;
		}

		public function pushAllOrders($startingPage=1, $stopPage=false){
			$page = $startingPage;
			do{
				$wc_orders_batch = $this->getWCOrders($page, 50);

				foreach($wc_orders_batch as $order):
					/* Push to Fetch */
					$order_id = $order->ID;

					if(! $this->fetchapp_send_incomplete_orders): /* If we don't send incomplete orders */
						
						if($order->post_status != 'wc-completed'): /* If it's not completed, don't send it, so return */
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

				$page++;
				if($stopPage !== false && $page > $stopPage):
					break;
				endif;
			}
			while(count($wc_orders_batch) > 0);
		}

		public function syncAllOrders($startingPage=1, $stopPage=false){
			$this->pullAllOrders($startingPage, $stopPage);
			$this->pushAllOrders($startingPage, $stopPage);	
		}

		public function pullProductsFromFetch($page, $per_page){
			$products = array();
			try{
			    $products = $this->fetchApp->getProducts($per_page, $page);
			}
			catch (Exception $e){
				// This will occur on any call if the AuthenticationKey and AuthenticationToken are not set.
    			$this->showMessage("FetchApp Debug:".$e->getMessage() );
			}
			return $products;
		}

		public function pullOrdersFromFetch($page, $per_page){
			$orders = array();
			try{
				// 2 = Order Status All
			    $orders = $this->fetchApp->getOrders(2, $per_page, $page);
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
			if($fetchapp_scheduled_sync = get_option('fetchapp_scheduled_sync') && isset($fetchapp_scheduled_sync) && $fetchapp_scheduled_sync == '1'):
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
			$startingPage = 1;

			if(isset($_POST['starting_page']) && $_POST['starting_page'] > 0):
				$startingPage = (int)$_POST['starting_page'];
			endif;	

			$stopPage = false;

			if(isset($_POST['stop_page']) && $_POST['stop_page'] > 0):
				$stopPage = (int)$_POST['stop_page'];
			endif;	

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

				if(isset($_POST['fetchapp_sync_order_number']) && $_POST['fetchapp_sync_order_number']):
					update_option('fetchapp_sync_order_number', '1');
				else:
					update_option('fetchapp_sync_order_number', '0');
				endif;

				$this->message = "Settings Updated";
				$this->showMessage("Settings Updated");
				// TODO: Validate Key / Token
			endif;

			if(isset($_POST['sync_orders'])):
				$this->syncAllOrders($startingPage, $stopPage);
				$this->showMessage("Orders synchronized with FetchApp");
			endif;

			if(isset($_POST['pull_orders'])):
				$this->pullAllOrders($startingPage, $stopPage);
				$this->showMessage("Orders pulled from FetchApp");
			endif;

			if(isset($_POST['push_orders'])):
				$this->pushAllOrders($startingPage, $stopPage);
				$this->showMessage("Orders pushed to FetchApp");
			endif;

			if(isset($_POST['sync_products'])):
				$this->syncAllProducts($startingPage, $stopPage);
				$this->showMessage("Products synchronized with FetchApp");
			endif;

			if(isset($_POST['pull_products'])):
				$this->pullAllProducts($startingPage, $stopPage);
				$this->showMessage("Products pulled from FetchApp");
			endif;

			if(isset($_POST['push_products'])):
				$this->pushAllProducts($startingPage, $stopPage);
				$this->showMessage("Products pushed to FetchApp");
			endif;

			echo "<div>
			<h2>FetchApp Settings</h2>
			<form method=\"post\">
			";
			echo  settings_fields('plugin_options').do_settings_sections('fetchapp_wc_settings');
			echo "<br /><br /><input name=\"update_fetchapp_settings\" type=\"submit\" value=\"Save Settings\" /><br /><br />";

			echo "<h3>Sync Actions</h3>";
			echo "<h4>Pagination</h4>";
				echo "<p>If you experience timeouts when syncing, you can set a start and stop page for all sync actions<p>";
				echo "<div>";
					echo "<label>Start Page: </label><input type='text' name='starting_page' style='width: 50px; margin-right: 0.5rem;' />";
					echo "<label>Stop Page: </label><input type='text' name='stop_page' style='width: 50px' />";
				echo "</div>";
			echo "<h4>Orders</h4>";
			echo "<input name=\"sync_orders\" type=\"submit\" value=\"Sync (Pull + Push) Orders\" /> | ";
			echo "<input name=\"pull_orders\" type=\"submit\" value=\"Pull Orders\" /> | ";
			echo "<input name=\"push_orders\" type=\"submit\" value=\"Push Orders\" />";
			echo "<h4>Products</h4>";
			echo "<input name=\"sync_products\" type=\"submit\" value=\"Sync (Pull + Push) Products\" /> | ";
			echo "<input name=\"pull_products\" type=\"submit\" value=\"Pull Products\" /> | ";
			echo "<input name=\"push_products\" type=\"submit\" value=\"Push Products\" />";
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

			add_settings_section('fetchapp_order_sync_header', 'Order Sync', array($this, 'plugin_section_text'), 'fetchapp_wc_settings');
			add_settings_field('fetchapp_sync_order_number', 'Syncronize Orders to FetchApp based on Order Number', array($this, 'fetchapp_sync_order_number_string'), 'fetchapp_wc_settings', 'fetchapp_order_sync_header');

		}
		
		public function plugin_section_text() {
			echo '';
		}

		public function fetchapp_token_validate($input){
			$options = get_option('fetchapp_token');
			$options = trim($input);
			return $options;
		}

		public function fetchapp_key_validate($input){
			$options = get_option('fetchapp_key');
			$options = trim($input);
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

			if(is_array($options) && isset($options['text_string'])):
				$options = $options['text_string'];
			endif;

			echo "<input id='fetchapp_key_string' name='fetchapp_key' size='40' type='text' value='{$options}' />";
		}


		public function fetchapp_token_string() {
			$options = get_option('fetchapp_token');

			if(is_array($options) && isset($options['text_string'])):
				$options = $options['text_string'];
			endif;

			echo "<input id='fetchapp_token' name='fetchapp_token' size='40' type='text' value='{$options}' />";
		}

		public function fetchapp_debug_string() {
			$options = get_option('fetchapp_debug_mode');
			echo "<input id='fetchapp_debug_mode' name='fetchapp_debug_mode' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_use_ssl_string() {
			$options = get_option('fetchapp_use_ssl');
			echo "<input id='fetchapp_use_ssl' name='fetchapp_use_ssl' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_sync_order_number_string() {
			$options = get_option('fetchapp_sync_order_number');
			echo "<select id='fetchapp_sync_order_number' name='fetchapp_sync_order_number'>";
				echo "<option value='1'".selected($options, 1, false).">WooCommerce Order Number</option>";
				echo "<option value='0'".selected($options, 0, false).">Wordpress Post ID</option>";
			echo "</select>";
			echo "<div><label for='fetchapp_sync_order_number'>By default, FetchApp for WooCommerce uses the Wordpress Post ID to synchronize orders. You may instead use the WooCommerce Order Number.<label></div>";
		}

		public function fetchapp_scheduled_sync_string() {
			$options = get_option('fetchapp_scheduled_sync');
			echo "<input id='fetchapp_scheduled_sync' name='fetchapp_scheduled_sync' type='checkbox' value='1' ".checked($options, 1, false)." />";
		}

		public function fetchapp_send_incomplete_orders_string() {
			$options = get_option('fetchapp_send_incomplete_orders');
			echo "<input id='fetchapp_send_incomplete_orders' name='fetchapp_send_incomplete_orders' type='checkbox' value='1' ".checked($options, 1, false)." />";
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
		        'yes'       => __('Sync with FetchApp', 'wpse'),
		    );

		    $checked_yes = ""; 
		    $checked_no = "checked='checked'"; 
		    if($saved == 'yes'):
		    	$checked_yes = "checked='checked'";
		    	$checked_no = "";
		    endif;

		    foreach($fields as $key => $label)
		    {
		        printf(
		            '<label for="_fetchapp_sync[%1$s]"> %2$s ' .
		            '</label><br>'.
		            '<input id="fetchapp_yes" type="radio" name="_fetchapp_sync" value="%1$s" id="_fetchapp_sync[%1$s]" '.$checked_yes.' /> <label for="fetchapp_yes">Yes</label> <br/>'.
		            '<input id="fetchapp_no" type="radio" name="_fetchapp_sync" value="no" id="_fetchapp_sync[%1$s]" '.$checked_no.'  /> <label for="fetchapp_no">No</label>',
		            esc_attr($key),
		            esc_html($label),
		            checked($saved, $key, false)
		        );
		    }

		   $fetchapp_id = get_post_meta( $post->ID, '_fetchapp_id', true);
		   $fetchapp_system_id = get_post_meta( $post->ID, '_fetchapp_system_id', true);
		 	if($fetchapp_id):
		 		if($post->post_type == 'product'):
			 		echo "<br /><label>FetchApp SKU:</label> <strong>{$fetchapp_id}</strong>";
			 		if($fetchapp_system_id):
				 		echo "<br /><label>FetchApp ID:</label> <strong>{$fetchapp_system_id}</strong>";
				 	endif;
		 		else:
		 			echo "<br /><label>FetchApp Order ID:</label> <strong>{$fetchapp_id}</strong>";
		 		endif;
		 	endif;
		}
		/* End Admin Screen Functions */
	}
endif;