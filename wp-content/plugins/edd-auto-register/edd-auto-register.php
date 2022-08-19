<?php
/**
 * Plugin Name: Easy Digital Downloads - Auto Register
 * Plugin URI:  https://easydigitaldownloads.com/downloads/auto-register/
 * Description: Automatically creates a WP user account at checkout, based on customer's email address.
 * Version:     1.4.3
 * Author:      Easy Digital Downloads
 * Author URI:  https://easydigitaldownloads.com
 * Text Domain: edd-auto-register
 * Domain Path: languages
 * Requires at least: 4.4
 * Requires PHP:      5.4
 * License:     GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Auto_Register' ) ) {

	final class EDD_Auto_Register {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of EDD Auto Register exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * The plugin version.
		 *
		 * @var string
		 */
		public $version = '1.4.3';

		/**
		 * Path to the plugin file.
		 *
		 * @var string
		 */
		public $file;

		/**
		 * Path to the plugin's directory.
		 *
		 * @var string
		 */
		public $plugin_dir;

		/**
		 * URL to the plugin's directory.
		 *
		 * @var string
		 */
		public $plugin_url;

		/**
		 * The auto register emails class.
		 *
		 * @since 1.4
		 * @var EDD\Auto_Register\Emails
		 */
		public $emails;

		/**
		 * Main Instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 *
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Auto_Register ) ) {
				self::$instance = new EDD_Auto_Register;
				self::$instance->setup_globals();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Constructor Function
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {
			self::$instance = $this;

		}

		/**
		 * Reset the instance of the class
		 *
		 * @since 1.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Globals
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_globals() {

			$this->file       = __FILE__;
			$this->basename   = apply_filters( 'edd_auto_register_plugin_basenname', plugin_basename( $this->file ) );
			$this->plugin_dir = apply_filters( 'edd_auto_register_plugin_dir_path',  plugin_dir_path( $this->file ) );
			$this->plugin_url = apply_filters( 'edd_auto_register_plugin_dir_url',   plugin_dir_url( $this->file ) );
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function hooks() {

			// Force guest checkout to be enabled
			add_filter( 'edd_get_option_logged_in_only', '__return_false' );

			// Return if guest checkout is disabled
			if ( edd_no_guest_checkout() || apply_filters( 'edd_auto_register_disable', false ) ) {
				return;
			}

			require_once $this->plugin_dir . 'includes/emails.php';
			$this->emails = new \EDD\Auto_Register\Emails();

			// text domain
			add_action( 'after_setup_theme', array( $this, 'load_textdomain' ) );

			// add settings
			add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_section' ) );
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );
			add_filter( 'edd_settings_gateways', array( $this, 'modify_guest_checkout' ) );
			add_filter( 'edd_after_setting_output', array( $this, 'disable_guest_checkout' ), 10, 2 );

			// can the customer checkout?
			add_filter( 'edd_can_checkout', array( $this, 'can_checkout' ) );

			// create user when purchase is created
			add_action( 'edd_payment_saved', array( $this, 'maybe_insert_user' ), 10, 2 );
			add_action( 'edd_post_add_manual_order', array( $this, 'insert_user_during_manual_order' ), 10, 3 );

			// add our new email notifications
			add_action( 'edd_auto_register_insert_user', array( $this->emails, 'email_notifications' ), 10, 2 );

			// Add new email tags: {set_password_link} and {login_link}
			add_action( 'edd_add_email_tags', array( $this->emails, 'add_email_tag' ), 100 );

			// Ensure registration form is never shown
			add_filter( 'edd_get_option_show_register_form', array( $this, 'remove_register_form' ), 10, 3 );
			add_filter( 'edd_settings_gateways', array( $this, 'modify_register_form' ) );

			do_action( 'edd_auto_register_setup_actions' );
		}

		/**
		 * Admin notices
		 *
		 * @since 1.0
		 */
		public function admin_notices() {
			_deprecated_function( __FUNCTION__, '1.4' );
			echo '<div class="error"><p>' . __( 'EDD Auto Register requires Easy Digital Downloads Version 2.3 or greater. Please update or install Easy Digital Downloads.', 'edd-auto-register' ) . '</p></div>';
		}


		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		public function load_textdomain() {
			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_auto_register_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd-auto-register' );
			$mofile        = sprintf( '%1$s-%2$s.mo', 'edd-auto-register', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-auto-register/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-auto-register folder
				load_textdomain( 'edd-auto-register', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-auto-register/languages/ folder
				load_textdomain( 'edd-auto-register', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-auto-register', false, $lang_dir );
			}
		}


		/**
		 * Notifications
		 * Sends the user an email with their logins details and also sends the site admin an email notifying them of a signup
		 *
		 * @since 1.1
		 * @deprecated 1.4
		 */
		public function email_notifications( $user_id = 0, $user_data = array() ) {
			_deprecated_function( __FUNCTION__, '1.4', 'edd_auto_register()->emails->email_notifications' );
			$this->emails->email_notifications( $user_id, $user_data );
		}

		/**
		 * Email Template Body
		 *
		 * @since 1.0
		 * @deprecated 1.4
		 * @return string $default_email_body Body of the email
		 */
		public function get_email_body_content( $first_name, $username, $password ) {
			_deprecated_function( __FUNCTION__, '1.4' );
			$user = get_user_by( 'login', $username );

			return $this->emails->get_message( $user );
		}

		/**
		 * Can checkout?
		 * Prevents the form from being displayed when User must be logged in (Guest Checkout disabled), but "Show Register / Login Form?" is not
		 *
		 * @since 1.0
		 */
		public function can_checkout( $can_checkout ) {

			if ( edd_no_guest_checkout() && ! edd_get_option( 'show_register_form' ) && ! is_user_logged_in() ) {
				return false;
			}

			return $can_checkout;
		}

		/**
		 * When a payment is inserted, possibly registers a user
		 *
		 * If this is the first purchase, disables the EDD Core user verification system
		 *
		 * @since 1.3
		 */
		public function maybe_insert_user( $payment_id, $payment ) {

			if ( did_action( 'edd_batch_import_class_include' ) ) {
				$this->create_user_during_import( $payment_id, $payment );
				return;
			}

			// stop EDD from sending new user notification, we want to customize this a bit
			remove_action( 'edd_insert_user', 'edd_new_user_notification', 10, 2 );

			edd_debug_log( 'EDDAR: maybe_insert_user running...' );
			edd_debug_log( 'Payment: ' . print_r( $payment, true ) );

			// This function only creates users using a Payment. If the payment ID is empty, we can't do that.
			if ( empty( $payment->ID ) ) {
				return false;
			}

			// If the user is not logged in
			if ( ! is_user_logged_in() ) {

				$customer    = new EDD_Customer( $payment->email );
				$payment_ids = explode( ',', $customer->payment_ids );

				if ( is_array( $payment_ids ) && ! empty( $payment_ids ) ) {

					$payment_ids = array_map( 'absint', $payment_ids );

					// If the payment inserted is the only payment, we don't need verification
					if ( 1 === count( $payment_ids ) && in_array( $payment_id, $payment_ids ) ) {
						remove_action( 'user_register', 'edd_connect_existing_customer_to_new_user', 10, 1 );
						remove_action( 'user_register', 'edd_add_past_purchases_to_new_user', 10, 1 );
					}

				}

				$user_id = $this->create_user( array(), $payment_id );

			} else {

				if( function_exists( 'did_action' ) && ! did_action( 'edd_create_payment' ) ) {

					// Don't use the current user ID when creating payments through Manual Purchases
					$user_id = get_current_user_id();

				}
			}

			// Validate inserted user
			if ( empty( $user_id ) || is_wp_error( $user_id ) ) {
				return;
			}

			$payment_meta = edd_get_payment_meta( $payment_id );

			$payment_meta['user_info']['id'] = $user_id;

			edd_update_payment_meta( $payment_id, '_edd_payment_user_id', $user_id );
			edd_update_payment_meta( $payment_id, '_edd_payment_meta', $payment_meta );

		}

		/**
		 * Adds a new user when an order is manually created in EDD 3.0.
		 *
		 * @since 1.4
		 * @param int    $order_id   The order ID.
		 * @param array  $order_data The array of order data.
		 * @param array  $args       The original form data.
		 * @return void
		 */
		public function insert_user_during_manual_order( $order_id, $order_data, $args ) {
			if ( empty( $args['edd-new-customer'] ) ) {
				return;
			}

			$this->create_user( array(), $order_id );
		}

		/**
		 * Creates a user account during payment import
		 *
		 * @since  1.4
		 * @param  int              $payment_id   The payment ID
		 * @param  bool|EDD_Payment $payment The EDD_Payment object
		 * @return void
		 */
		public function create_user_during_import( $payment_id = 0, $payment = false ) {

			if ( ! did_action( 'edd_batch_import_class_include' ) ) {
				return;
			}
			// Remove standard actions/emails which happen when a payment is saved.
			remove_action( 'edd_customer_post_attach_payment', 'edd_connect_guest_customer_to_existing_user' );
			remove_action( 'edd_auto_register_insert_user', array( $this->emails, 'email_notifications' ) );
			remove_action( 'edd_insert_user', 'edd_new_user_notification' );
			remove_action( 'user_register', 'edd_add_past_purchases_to_new_user' );
			remove_action( 'edd_admin_sale_notice', 'edd_admin_email_notice' );

			$this->create_user( array(), $payment_id );
		}

		/**
		 * Processes the supplied payment data to possibly register a user
		 *
		 * @since  1.3.3
		 * @param  array   $deprecated The Payment data (deprecated in 1.4)
		 * @param  int     $payment_id   The payment ID
		 * @return int|WP_Error          The User ID created or an instance of WP_Error if the insert fails
		 */
		public function create_user( $deprecated = array(), $payment_id = 0 ) {

			$payment   = $this->get_payment( $payment_id, $deprecated );
			$user_name = sanitize_user( $payment->email );
			if ( ! $this->can_create_user( $payment, $user_name ) ) {
				return false;
			}

			// Since this filter existed before, we must send in a $payment_id, which we default to false if none is supplied
			$user_args = apply_filters( 'edd_auto_register_insert_user_args', array(
				'user_login'      => $user_name,
				'user_pass'       => wp_generate_password( 32 ),
				'user_email'      => $payment->email,
				'first_name'      => $payment->first_name,
				'last_name'       => $payment->last_name,
				'user_registered' => date( 'Y-m-d H:i:s' ),
				'role'            => get_option( 'default_role' )
			), $payment_id, $deprecated );

			// Insert new user
			$user_id = wp_insert_user( $user_args );

			if ( ! is_wp_error( $user_id ) ) {

				$this->maybe_add_address( $user_id, $payment );

				// Allow themes and plugins to hook
				do_action( 'edd_auto_register_insert_user', $user_id, $user_args, $payment_id );

				$maybe_login_user = function_exists( 'did_action' ) && ( did_action( 'edd_purchase' ) || did_action( 'edd_straight_to_gateway' ) || did_action( 'edd_free_download_process' ) );
				$maybe_login_user = apply_filters( 'edd_auto_register_login_user', $maybe_login_user );

				if ( true === $maybe_login_user ) {

					edd_log_user_in( $user_id, $user_args['user_login'], $user_args['user_pass'] );

				}

				$customer = new EDD_Customer( $payment->email );
				$customer->update( array( 'user_id' => $user_id ) );
			}

			return $user_id;
		}

		/**
		 * Gets the payment object for creating the user.
		 * If the payment ID doesn't exist yet (because of Recurring, for example),
		 * this manually creates a payment object with the minimum user data needed.
		 *
		 * @since 1.4.1
		 * @param int   $payment_id
		 * @param array $purchase_data
		 * @return EDD_Payment
		 */
		private function get_payment( $payment_id, $purchase_data = array() ) {
			if ( ! empty( $payment_id ) || empty( $purchase_data ) ) {
				return new EDD_Payment( $payment_id );
			}
			$payment = new EDD_Payment();
			if ( ! empty( $purchase_data['user_info']['email'] ) ) {
				$payment->__set( 'email', $purchase_data['user_info']['email'] );
			}
			if ( ! empty( $purchase_data['user_info']['first_name'] ) ) {
				$payment->__set( 'first_name', $purchase_data['user_info']['first_name'] );
			}
			if ( ! empty( $purchase_data['user_info']['last_name'] ) ) {
				$payment->__set( 'last_name', $purchase_data['user_info']['last_name'] );
			}
			if ( ! empty( $purchase_data['user_info']['address'] ) ) {
				$payment->__set( 'address', $purchase_data['user_info']['address'] );
			}
			if ( ! empty( $purchase_data['downloads'] ) ) {
				$payment->__set( 'downloads', $purchase_data['downloads'] );
			}
			if ( ! empty( $purchase_data['cart_details'] ) ) {
				$payment->__set( 'cart_details', $purchase_data['cart_details'] );
			}
			if ( ! empty( $purchase_data['status'] ) ) {
				$payment->__set( 'status', $purchase_data['status'] );
			}

			return $payment;
		}

		/**
		 * Whether a new user account can be created for an order.
		 *
		 * @since 1.4
		 * @param EDD_Payment $payment    The array of payment data.
		 * @param string      $user_name  The user name to check.
		 * @return bool
		 */
		private function can_create_user( $payment, $user_name ) {

			if ( empty( $user_name ) ) {
				return false;
			}

			$user = get_user_by( 'email', $payment->email );
			// User account already exists.
			if ( $user instanceof WP_User ) {
				// For multisite, associate the user with the site.
				if ( is_multisite() ) {
					add_user_to_blog( get_current_blog_id(), $user->ID, get_option( 'default_role' ) );
				}
				return false;
			}

			// Username already exists
			if ( username_exists( $user_name ) ) {
				return false;
			}

			$can_create = true;

			// If Auto Register is enabled only for complete orders and the order is not complete, the user cannot be created.
			if ( ! in_array( $payment->status, array( 'complete', 'publish', 'completed' ), true ) && edd_get_option( 'edd_auto_register_complete_orders_only' ) ) {
				$can_create = false;
			}

			/**
			 * Allow developers to modify whethe a user can be created.
			 *
			 * @since 1.4
			 * @param bool        Whether the user can be registered.
			 * @param EDD_Payment The payment object
			 * @param string      The new user name that's been checked.
			 */
			return apply_filters( 'edd_auto_register_can_create_user', $can_create, $payment, $user_name );
		}

		/**
		 * Maybe add the payment address to the user in EDD 2.x.
		 * In EDD 3.0, the address is automatically added to the customer.
		 *
		 * @since 1.4.3
		 * @param int         $user_id The newly registered user ID.
		 * @param EDD_Payment $payment The payment object.
		 * @return void
		 */
		private function maybe_add_address( $user_id, $payment ) {
			if ( function_exists( 'edd_add_customer_address' ) || empty( array_filter( $payment->address ) ) ) {
				return;
			}

			update_user_meta( $user_id, '_edd_user_address', $payment->address );
		}

		/**
		 * Add a settings section
		 *
		 * @param array $sections
		 *
		 * @since 1.3.14
		 * @return array
		 */
		public function settings_section( $sections ) {
			$sections['auto_register'] = __( 'Auto Register', 'edd-auto-register' );

			return $sections;
		}


		/**
		 * Settings
		 *
		 * @since 1.1
		 */
		public function settings( $settings ) {
			$edd_ar_settings = array(
				array(
					'id' => 'edd_auto_register_disable_user_email',
					'name' => __( 'Disable User Email', 'edd-auto-register' ),
					'desc' => __( 'Disables the email sent to the user that contains login details', 'edd-auto-register' ),
					'type' => 'checkbox',
				),
				array(
					'id' => 'edd_auto_register_disable_admin_email',
					'name' => __( 'Disable Admin Notification', 'edd-auto-register' ),
					'desc' => __( 'Disables the new user registration email sent to the admin', 'edd-auto-register' ),
					'type' => 'checkbox',
				),
				array(
					'id'   => 'edd_auto_register_complete_orders_only',
					'name' => __( 'Limit Auto Register', 'edd-auto-register' ),
					'desc' => __( 'Only register users for successful orders', 'edd-auto-register' ),
					'type' => 'checkbox',
				),
			);

			return array_merge( $settings, array( 'auto_register' => $edd_ar_settings ) );
		}

		/**
		 * Update the "Require Login" setting description and remove the checkbox.
		 *
		 * @since 1.4
		 * @param array $settings
		 * @return array
		 */
		public function modify_guest_checkout( $settings ) {
			if ( ! empty( $settings['checkout']['logged_in_only'] ) ) {
				$settings['checkout']['logged_in_only']['desc']                = __( 'Because Auto Register is active, this setting does not apply. Guest customers will automatically have an account created during checkout.', 'edd-auto-register' );
				$settings['checkout']['logged_in_only']['field_class']         = 'disabled';
				$settings['checkout']['logged_in_only']['options']['disabled'] = true;
				unset( $settings['checkout']['logged_in_only']['tooltip_title'] );
				unset( $settings['checkout']['logged_in_only']['tooltip_desc'] );
			}

			return $settings;
		}

		/**
		 * Modifies the HTML for the logged_in_only checkbox to mark it as disabled.
		 *
		 * @since 1.4
		 * @param string $html The markup for the setting field.
		 * @param array  $args  The arguments passed to render the setting field.
		 *
		 * @return string
		 */
		public function disable_guest_checkout( $html, $args ) {

			if ( function_exists( 'edd_get_order' ) ) {
				return $html;
			}
			// Only modify the Checkout > Logged in Only setting.
			if ( empty( $args['section'] ) || 'checkout' !== $args['section'] || 'logged_in_only' !== $args['id'] ) {
				return $html;
			}

			return str_replace( 'class="disabled', 'disabled class="disabled', $html );
		}

		/**
		 * Hide the registration form on checkout
		 *
		 * @since 1.3
		 */
		public function remove_register_form( $value, $key, $default ) {

			if ( 'both' === $value ){
				$value = 'login';
			} elseif ( 'registration' === $value ) {
				$value = 'none';
			}

			return $value;
		}

		/**
		 * Modifies the show_register_form setting options and description.
		 *
		 * @since 1.4
		 * @param array $settings The array of gateway settings.
		 * @return array
		 */
		public function modify_register_form( $settings ) {
			if ( ! empty( $settings['checkout']['show_register_form']['options'] ) ) {
				unset( $settings['checkout']['show_register_form']['options']['both'] );
				unset( $settings['checkout']['show_register_form']['options']['registration'] );
				$settings['checkout']['show_register_form']['name'] = __( 'Show Login Form?', 'edd-auto-register' );
				$settings['checkout']['show_register_form']['desc'] = __( 'Optionally display the login form on the checkout page for non-logged-in users.', 'edd-auto-register' );
			}

			return $settings;
		}

	}
}

/**
 * Loads a single instance of EDD Auto Register
 *
 * This follows the PHP singleton design pattern.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @example <?php $edd_auto_register = edd_auto_register(); ?>
 *
 * @since 1.0
 *
 * @see EDD_Auto_Register::get_instance()
 *
 * @return object Returns an instance of the EDD_Auto_Register class
 */
function edd_auto_register() {
	return EDD_Auto_Register::get_instance();
}

require_once dirname( __FILE__ ) . '/vendor/autoload.php';
\EDD\ExtensionUtils\v1\ExtensionLoader::loadOrQuit( __FILE__, 'edd_auto_register', array(
	'php'                    => '5.4',
	'easy-digital-downloads' => '2.9',
	'wp'                     => '4.4',
) );
