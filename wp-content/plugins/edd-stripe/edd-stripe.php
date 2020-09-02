<?php
/*
Plugin Name: Easy Digital Downloads - Stripe Payment Gateway
Plugin URL: https://easydigitaldownloads.com/downloads/stripe-gateway/
Description: Adds a payment gateway for Stripe.com
Version: 2.7.7
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Text Domain: edds
Domain Path: languages
*/

class EDD_Stripe {

	private static $instance;

	public $rate_limiting;

	private function __construct() {

	}

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Stripe ) ) {
			self::$instance = new EDD_Stripe;

			if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {

				add_action( 'admin_notices', self::below_php_version_notice() );

			} else {

				self::$instance->setup_constants();

				add_action( 'init', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
				self::$instance->setup_classes();
				self::$instance->actions();
				self::$instance->filters();


				if ( class_exists( 'EDD_License' ) && is_admin() ) {
					new EDD_License( __FILE__, 'Stripe Payment Gateway', EDD_STRIPE_VERSION, 'Easy Digital Downloads', 'stripe_license_key' );
				}

			}
		}

		return self::$instance;
	}

	function below_php_version_notice() {
		echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by Easy Digital Downloads - Stripe Payment Gateway. Please contact your host and request that your version be upgraded to 5.6.0 or greater.', 'edds' ) . '</p></div>';
	}

	private function setup_constants() {
		if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
			define( 'EDDS_PLUGIN_DIR', dirname( __FILE__ ) );
		}

		if ( ! defined( 'EDDSTRIPE_PLUGIN_URL' ) ) {
			define( 'EDDSTRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		define( 'EDD_STRIPE_VERSION', '2.7.7' );

		// To be used with \Stripe\Stripe::setApiVersion.
		define( 'EDD_STRIPE_API_VERSION', '2019-08-14' );

		// To be used with \Stripe\Stripe::setAppInfo.
		define( 'EDD_STRIPE_PARTNER_ID', 'pp_partner_DKh7NDe3Y5G8XG' );
	}

	private function includes() {
		if ( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once EDDS_PLUGIN_DIR . '/vendor/autoload.php';
		}

		require_once EDDS_PLUGIN_DIR . '/includes/class-stripe-api.php';

		require_once EDDS_PLUGIN_DIR . '/includes/deprecated.php';
		require_once EDDS_PLUGIN_DIR . '/includes/compat.php';

		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-attribute-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-object-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/interface-static-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/class-registry.php';

		require_once EDDS_PLUGIN_DIR . '/includes/emails.php';
		require_once EDDS_PLUGIN_DIR . '/includes/payment-receipt.php';
		require_once EDDS_PLUGIN_DIR . '/includes/card-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-filters.php';
		require_once EDDS_PLUGIN_DIR . '/includes/payment-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/webhooks.php';
		require_once EDDS_PLUGIN_DIR . '/includes/elements.php';
		require_once EDDS_PLUGIN_DIR . '/includes/scripts.php';
		require_once EDDS_PLUGIN_DIR . '/includes/template-functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/class-edd-stripe-rate-limiting.php';

		if ( is_admin() ) {
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/notices.php';

			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-actions.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-filters.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/settings.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/upgrade-functions.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/reporting/class-stripe-reports.php';
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/wp-cli.php';
		}

	}

	private function actions() {
		add_action( 'admin_init', array( self::$instance, 'database_upgrades' ) );
	}

	private function filters() {
		add_filter( 'edd_payment_gateways', array( self::$instance, 'register_gateway' ) );
	}

	private function setup_classes() {
		$this->rate_limiting = new EDD_Stripe_Rate_Limiting();
	}

	public function database_upgrades() {
		$did_upgrade = false;
		$version     = get_option( 'edds_stripe_version' );

		if( ! $version || version_compare( $version, EDD_STRIPE_VERSION, '<' ) ) {

			$did_upgrade = true;

			switch( EDD_STRIPE_VERSION ) {

				case '2.5.8' :
					edd_update_option( 'stripe_checkout_remember', true );
					break;

			}

		}

		if( $did_upgrade ) {
			update_option( 'edds_stripe_version', EDD_STRIPE_VERSION );
		}
	}

	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = EDDS_PLUGIN_DIR . '/languages/';

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'edds' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'edds', $locale );

		// Setup paths to current locale file
		$mofile_local   = $lang_dir . $mofile;
		$mofile_global  = WP_LANG_DIR . '/edd-stripe/' . $mofile;

		// Look in global /wp-content/languages/edd-stripe/ folder
		if( file_exists( $mofile_global ) ) {
			load_textdomain( 'edds', $mofile_global );

		// Look in local /wp-content/plugins/edd-stripe/languages/ folder
		} elseif( file_exists( $mofile_local ) ) {
			load_textdomain( 'edds', $mofile_local );

		} else {
			// Load the default language files
			load_plugin_textdomain( 'edds', false, $lang_dir );
		}
	}

	public function register_gateway( $gateways ) {
		// Format: ID => Name
		$gateways['stripe'] = array(
			'admin_label'    => 'Stripe',
			'checkout_label' => __( 'Credit Card', 'edds' ),
			'supports'       => array(
				'buy_now'
			)
		);
		return $gateways;
	}


}

function edd_stripe() {

	if( ! function_exists( 'EDD' ) ) {
		return;
	}

	return EDD_Stripe::instance();
}
add_action( 'plugins_loaded', 'edd_stripe', 10 );

/**
 * Plugin activation
 *
 * @since       2.5.7
 * @return      void
 */
function edds_plugin_activation() {

	if( ! function_exists( 'EDD' ) ) {
		return;
	}

	global $edd_options;

	/*
	 * Migrate settings from old 3rd party gateway
	 *
	 * See https://github.com/easydigitaldownloads/edd-stripe/issues/153
	 *
	 */

	$changed = false;
	$options = get_option( 'edd_settings', array() );

	// Set checkout button text
	if( ! empty( $options['stripe_checkout_button_label'] ) && empty( $options['stripe_checkout_button_text'] ) ) {

		$options['stripe_checkout_button_text'] = $options['stripe_checkout_button_label'];

		$changed = true;

	}

	// Set checkout logo
	if( ! empty( $options['stripe_checkout_popup_image'] ) && empty( $options['stripe_checkout_image'] ) ) {

		$options['stripe_checkout_image'] = $options['stripe_checkout_popup_image'];

		$changed = true;

	}

	// Set billing address requirement
	if( ! empty( $options['require_billing_address'] ) && empty( $options['stripe_checkout_billing'] ) ) {

		$options['stripe_checkout_billing'] = 1;

		$changed = true;

	}


	if( $changed ) {

		$options['stripe_checkout'] = 1;
		$options['gateways']['stripe'] = 1;

		if( isset( $options['gateway']['stripe_checkout'] ) ) {
			unset( $options['gateway']['stripe_checkout'] );
		}

		$merged_options = array_merge( $edd_options, $options );
		$edd_options    = $merged_options;
		update_option( 'edd_settings', $merged_options );

	}

	edd_update_option( 'stripe_use_existing_cards', 1 );

	if( is_plugin_active( 'edd-stripe-gateway/edd-stripe-gateway.php' ) ) {
		deactivate_plugins( 'edd-stripe-gateway/edd-stripe-gateway.php' );
	}

}
register_activation_hook( __FILE__, 'edds_plugin_activation' );

/** Backwards compatibility functions */
/**
 * Database Upgrade actions
 *
 * @access      public
 * @since       2.5.8
 * @return      void
 */
function edds_plugin_database_upgrades() {
	edd_stripe()->database_upgrades();
}


/**
 * Internationalization
 *
 * @since       1.6.6
 * @return      void
 */
function edds_textdomain() {
	edd_stripe()->load_textdomain();
}

/**
 * Register our payment gateway
 *
 * @since       1.0
 * @return      array
 */
function edds_register_gateway( $gateways ) {
	return edd_stripe()->register_gateway( $gateways );
}
