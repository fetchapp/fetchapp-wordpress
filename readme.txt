=== Plugin Name ===
Contributors: prcapps
Donate link: http://www.prcapps.com/
Tags: fetchapp,woocommerce
Requires at least: 3.6
Tested up to: 3.9.1
Stable tag: 1.0.5
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

