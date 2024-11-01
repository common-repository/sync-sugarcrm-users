=== Sync SugarCRM Users ===
Contributors: sukum
Donate link: 
Tags: SugarCRM, CRM, users, accounts, contacts
Requires at least: 2.6
Tested up to: 4.5
Stable tag: 2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sync SugarCRM Users to WordPress and vice versa

== Description ==

This plugin pulls the user details from a given SugarCRM URL and if there are no corresponding users creates users in WordPress. 

Similarly it allows the user to sync selected WordPress users to SugarCRM as Accounts/Contacts/Users. 

Admin is logged in to SugarCRM transparently and can manage SugarCRM from Wordpress.

If 'auto sync' is checked, a user created in wordpress is automatically synced to SugarCRM as Accounts/Contacts/Users.

== Installation ==

1. Upload `sync-sugarcrm-users` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently asked questions ==

= Will the plugin overwrite or change existing users in WordPress ? =

No.

= Requirements ? =

At least PHP 5.2 and PHP SOAP extension.

== Screenshots ==

1. Config Page
2. Sync to WP
3. Sync to SugarCRM (2)
4. Sync to SugarCRM (1)
5. Manage SugarCRM

== Changelog ==

= 2.3 =
* Can sync WordPress users to Accounts/Contacts/Users.

= 2.2 =
* Added auto syncing Wordpress user to SugarCRM on registering.

= 2.1 =
* Added managing of SugarCRM in iframe.

= 2.0 =
* Added syncing of Wordpress users to SugarCRM.

= 1.0 =
* Released v1.0

== Upgrade notice ==

= 2.3 =
Can sync WordPress users to Accounts/Contacts/Users in v2.3.
