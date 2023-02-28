=== Plugin Name ===
Contributors: prcapps
Donate link: http://www.prcapps.com/
Tags: fetchapp,woocommerce
Requires at least: 3.6
Tested up to: 6.1.1
Stable tag: 1.9.1
WC requires at least: 3.6
WC tested up to: 7.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides integration between FetchApp and WooCommerce.

== Description ==

This plugin provides integration between FetchApp and WooCommerce.

<h3>Features:</h3>
<ul>
<li>Pull products and orders from FetchApp into WooCommerce</li>
<li>Push products and orders from WooCommerce up to FetchApp</li>
<li>Automatic synchronization of new orders placed through your store, including the email of product download links</li>
<li>Periodic synchronization using a scheduled WordPress task.</li>
</ul>

== Installation ==

1. Install the plugin.
1. Select the FetchApp section of the administrative panel, and enter your FetchApp API token and key
1. Synchronize your products by click the "Synchronize Products" or "Synchronize Orders" buttons

== Changelog ==

= 1.9.0 =
* [Major] Adds compatibility with FetchApp API V3
* [Feature] Add pagination for Push / Pull / Sync routines for Orders and Products
* [Compatibility] Update to ensure compatibility with WooCommerce 7.4.0 and Wordpress 6.1.1

= 1.8.0 =
* [Feature] Added option to use WooCommerce Order Number rather than Wordpress Post ID for Order Sync. 
* [Feature] Add individual options to Push / Pull Orders, rather than Sync (Pull + Push) 
* [Feature] Add individual options to Push / Pull Products, rather than Sync (Pull + Push) 
* [Compatibility] Update to ensure compatibility with WooCommerce 4.9.1 and Wordpress 5.6

= 1.7.2 =
* Bugfix update. Update to ensure compatibility with WooCommerce 4.5.1 and Wordpress 5.5.1

= 1.7.0 =
* Update to ensure compatibility with WooCommerce 4.5.1 and Wordpress 5.5.1

= 1.6.3 =
* Update to ensure compatibility with WooCommerce 3.8.1 and Wordpress 5.3

= 1.5.0 =
* Update to ensure compatibility with WooCommerce 3.6.5 and Wordpress 5.2.2
* Disabled calls to FetchApp API delete endpoint for products and orders. 

= 1.4.0 =
* Update to ensure compatibility with WooCommerce 3.6.4 and Wordpress 5.2.1

= 1.3.0 =
* [New Feature] WooCommerce Products now have a "published date" that matches their "created at" property in FetchApp. 

= 1.2.0 =
* Resolved issues with Product import from FetchApp.

= 1.1.1 =
* Added support for Wordpress 5 and PHP 7. Additional bugfixes on settings page. 

= 1.1.0 =
* Added support for Wordpress 5 and PHP 7. Fixed bugs on settings page. 

= 1.0.12 =
* Fixed issue where prodcuts would unlink from FetchApp after an order in rare cases.

= 1.0.11 =
* Update to respect account-level download limits on Orders. 

= 1.0.9 =
* Update to prevent Fetch sync from removing product descriptions in WooCommerce 

= 1.0.8 =
* More bugfixes. 

= 1.0.7 =
* More bugfixes. 

= 1.0.6 =
* Various bugfixes due to updates in WooCommerce. 

= 1.0.5 =
* We have made significant enhancements to the plugin to improve plugin stability and functionality. 
* Bug Fixes:
1. FetchApp emails should now be sending properly. We revised the WooCommerce hooks used for synchorization, and revised the default configuration for imported Products. 
1. Products imported from FetchApp are now marked Virtual and Downloadable, to allow for automatic completion and fullfillment of orders.
1. Orders marked as complete from the Orders list in WooCommerce are now immediately pushed to FetchApp and cause an email to be sent.  
1. Orders marked as complete due to successful payment are now properly pushed to FetchApp, even if the confirmation page is not reached. 
1. Various other bugfixes. 

= 1.0.2 =
* Miscellaneous bug fixes.

= 1.0.1 =
* Fixed bug that caused Appearance menu to disappear. 

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.0.1 =
* Fixed bug that caused Appearance menu to disappear. 

= 1.0 =
* Inital Release

