<?php
/**
 * Plugin Name: Easy Digital Downloads - Stripe Payment Gateway
 * Plugin URL: https://easydigitaldownloads.com/downloads/stripe-gateway/
 * Description: Adds a payment gateway for Stripe.com
 * Version: 2.8.1
 * Author: Sandhills Development, LLC
 * Author URI: https://sandhillsdev.com
 * Text Domain: edds
 * Domain Path: languages
 */

/**
 * Returns the one true instance of EDD_Stripe
 *
 * @since 2.8.1
 *
 * @return void|\EDD_Stripe EDD_Stripe instance or void if Easy Digital
 *                          Downloads is not active.
 */
function edd_stripe_bootstrap() {
	// Easy Digital Downloads is not active, do nothing.
	if ( ! function_exists( 'EDD' ) ) {
		return;
	}

	// Stripe is already active, do nothing.
	if ( class_exists( 'EDD_Stripe' ) ) {
		return;
	}

	if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
		define( 'EDDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'EDDSTRIPE_PLUGIN_URL' ) ) {
		define( 'EDDSTRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	if ( ! defined( 'EDD_STRIPE_VERSION' ) ) {
		define( 'EDD_STRIPE_VERSION', '2.8.1' );
	}

	if ( ! defined( 'EDD_STRIPE_API_VERSION' ) ) {
		define( 'EDD_STRIPE_API_VERSION', '2020-03-02' );
	}

	if ( ! defined( 'EDD_STRIPE_PARTNER_ID' ) ) {
		define( 'EDD_STRIPE_PARTNER_ID', 'pp_partner_DKh7NDe3Y5G8XG' );
	}

	include_once __DIR__ . '/includes/class-edd-stripe.php';

	// Initial instantiation.
	EDD_Stripe::instance();
}
add_action( 'plugins_loaded', 'edd_stripe_bootstrap' );
remove_action( 'plugins_loaded', 'edd_stripe_core_bootstrap' );
