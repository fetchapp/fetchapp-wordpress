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

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
	require_once('zz_fetchappwp_carts/wc_fetchapp.class.php');
endif;
