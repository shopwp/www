=== EDD Auto Register ===
Contributors: easydigitaldownloads, sumobi, mordauk, cklosows, mindctrl, littlerchicken
Donate link: https://easydigitaldownloads.com/donate/
Tags: easy digital downloads, digital downloads, e-downloads, edd, purchase, auto, register, registration, e-commerce
Requires at least: 4.4
Tested up to: 5.9
Requires PHP: 5.4
Stable tag: 1.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically creates a WP user account at checkout, based on customer's email address.

== Description ==

This plugin now requires [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/ "Easy Digital Downloads") 2.9 or greater.

Once activated, EDD Auto Register will create a WordPress user account for your customer at checkout, without the need for the customer to enter any additional information. This eliminates the need for the default EDD registration form, and drastically reduces the time it takes your customers to complete their purchase.

Guest checkout is required so the plugin overrides the setting. The registration form is hidden on checkout while the plugin is active.

There are various filters available for developers, see the FAQ tab for more information.

**More extensions for Easy Digital Downloads**

You can find more extensions (both free and commercial) from [Easy Digital Downloads' website](https://easydigitaldownloads.com/downloads/ "Easy Digital Downloads")

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin
1. That's it! user accounts will automatically be created for your customers when they purchase your product for the first time and their login details will be emailed to them

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name


== Frequently Asked Questions ==

= How can I modify some of the key aspects of the plugin? =

There are filters available to modify the behaviour of the plugin:

1. edd_auto_register_email_subject
1. edd_auto_register_headers
1. edd_auto_register_insert_user_args
1. edd_auto_register_email_body
1. edd_auto_register_error_must_login
1. edd_auto_register_login_form
1. edd_auto_register_disable
1. edd_auto_register_can_create_user

= Can you provide a filter example of how to change the email's subject? =

Add the following to your child theme's functions.php

	function my_child_theme_edd_auto_register_email_subject( $subject ) {

		// enter your new subject below
		$subject = 'Here are your new login details';

		return $subject;

	}
	add_filter( 'edd_auto_register_email_subject', 'my_child_theme_edd_auto_register_email_subject' );


= Can you provide a filter example of how to change the email's body? =

Add the following to your child theme's functions.php

	function my_child_theme_edd_auto_register_email_body( $default_email_body, $first_name, $username, $password ) {
		$user = get_user_by( 'login', $username );
		$key  = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return false;
		}

		// Modify accordingly
		$message  = sprintf( __( 'Dear %s', 'edd-auto-register' ), $first_name ) . ",\n\n";
		$message .= __( 'Below are your login details:', 'edd-auto-register' ) . "\n\n";
		$message  = sprintf( __( 'Your Username: %s', 'edd-auto-register' ), sanitize_user( $username, true ) ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
		$message .= network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $username ), 'login' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Login: %s', 'edd-auto-register' ), wp_login_url() ) . "\r\n";

		return $message;
	}
	add_filter( 'edd_auto_register_email_body', 'my_child_theme_edd_auto_register_email_body', 10, 4 );

= Can you provide an example how to disable auto register?

Add the following to your child theme's functions.php to disable auto register based on the products purchased.

	add_filter( 'edd_auto_register_can_create_user', 'prefix_auto_register_can_create_user', 10, 3 );
	/**
	* Filters whether a user can be created for an order.
	*
	* @param bool        $can_create_user
	* @param EDD_Payment $payment
	* @param string      $user_name
	* @return bool
	*/
	function prefix_auto_register_can_create_user( $can_create_user, $payment, $user_name ) {
		// Set up the array of items in the cart.
		$items = array();
		foreach ( $payment->cart_details as $item ) {
			$items[] = $item['id'];
		}

		// Which items are valid for creating a user account.
		$items_for_auto_register = array( 2092 );

		// If there are no downloads that require auto register then disable it.
		if ( ! array_intersect( $items, $items_for_auto_register ) ) {
			return false;
		}

		return $can_create_user;
	}

= How can I disable the email from sending to the customer? =

There's an option under downloads &rarr; settings &rarr; extensions

== Screenshots ==

1. The standard purchase form which will create a user account from the customer's Email Address
1. The plugin's simple login form when both "Disable Guest Checkout" and "Show Register / Login Form?" are enabled
1. The error message that shows when "Disable Guest Checkout" is enabled, but "Show Register / Login Form?" is not

== Upgrade Notice ==

== Changelog ==
= Version 1.4.3, June 23, 2022 =
* Fix: The customer address is not added by Auto Register in EDD 3.0 as it's done in core.
* Fix: For sites which had customized the user notification to include the password in plain text, the hashed password was being sent instead.

= Version 1.4.2, May 12, 2022 =
* New: Added an option to only register new users for off-site gateways when payments are successful.
* Fix: Auto-registering users on multisite installs would throw an undefined variable notice.
* Fix: Viewing the Invoices settings would throw an undefined variable notice.

= Version 1.4.1, April 22, 2022 =
* Fix: Auto Register prevented new subscriptions from being purchased.
* Fix: A deprecation notice was being shown in PHP 8.0.

= Version 1.4, April 20, 2022 =
* Fix: Guest checkout settings were confusing when Auto-Register was active.
* Fix: User address information was not always saved.
* New: Added email tags to include Auto Register account information in the purchase receipt.
* New: Run Auto Register on manually created orders.
* New: Auto Register now works for CSV payment imports.
* New: `edd_auto_register_can_create_user` filter allows developers to modify whether a user can be created based on the payment data.
* Dev: Auto Register now implements the extension loader framework.

= Version 1.3.14, October 28, 2020 =

* Fix: New user email not sent when Auto Register is active.
* New: Add Danish translations.
* New: Add Auto Register section in EDD Extension settings.

= Version 1.3.13, October 22, 2019 =

* Fix: Fatal error when Easy Digital DOwnloads core is not active.

= Version 1.3.12, October 9, 2019 =
* Fix: Removed legacy edd_debug_log function declaration in order to avoid producing errors during EDD Updates.

= Version 1.3.11, July 26, 2019 =

* Fix: Fixed integration issue with Recurring Payments where payments were being prevented.
* New: Some EDD 3.0 compatibility improvements.
* New: Improved integration with Software Licensing by using recommended methods.
* New: Improved some debugging assistance code.
* New: Improved requiring EDD core's existence if Auto Register installed without it.

= Version 1.3.10, February 16, 2018 =

* Fix: User accounts not created with Free Downloads

= Version 1.3.9, April 27, 2017 =

* Fix: User not added to subsite when user already exists in site network

= 1.3.8 =

* Fix: Invalid foreach error when purchase does not contain license keys

= 1.3.7 =

* Fix: Ensure user ID is set on license keys properly

= 1.3.6 =

* Updated plugin authors

= 1.3.5 =
* Fix: Users not automatically logged in when using the Free Downloads extension

= 1.3.4 =

* Fix: Users not automatically logged in when using Buy Now buttons
* Fix: Manual purchases incorrectly assigned to site administrator that created the payment

= 1.3.3 =

* Tweak: Added support for other extensions to run the registration process before a payment is recorded
* Fix: Removed unused global variables
* Fix: Properly force Guest Checkout to be enabled

= 1.3.2 =
* Fix: Correct compatibility with Easy Digital Downloads user verification process.

= 1.3.1 =
* Fix: Issue with customers being forced to log in

= 1.3 =
* Fix: Resolves compatibility issues with Easy Digital Downloads 2.1+
* Fix: User accounts now created anytime a payment record is created, not just during checkout to resolve compatibility with some extensions
* Fix: Dramatically simplified code base

= 1.2.1 =
* Fix: EDD activation check

= 1.2 =
* Tweak: Pass $user_data along to edd_auto_register_insert_user_args filter
* Tweak: Pass username through sanitize_user() function

= 1.1 =
* New: User account creation now closely mimics that of EDD core meaning a user account will be created no matter what payment gateway is used
* New: "Lost Password?" link added to "login to purchase" form
* New: Setting to disable the admin notification
* New: Setting to disable the user notification
* New: edd_auto_register_insert_user_args filter. This can be used to do things such as modify the default role of the user when they are created
* Tweak: If a user who previously had an account returns to make a purchase it will no longer display "Email Address already in use". Instead it will be treated as a guest purchase
* Tweak: Email sent to user now includes login URL
* Tweak: Major code overhaul
* Tweak: New user email no longer uses the default EDD receipt template so it's not styled like a receipt if you have a custom template.

= 1.0.2 =
* New: Adding custom translations is now easier by adding them to the wp-content/languages/edd-auto-register folder
* New: Spanish and Catalan translations. Thanks to Joan Boluda!
* Fix: Undefined index errors when form was submitted without email address
* Fix: Text strings not being translated properly in registration email

= 1.0.1 =
* Fixed filter names for error messages

= 1.0 =
* Initial release
