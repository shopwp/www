<?php
/*
Plugin Name: Easy Digital Downloads - Purchase Gravatars
Plugin URI: http://sumobi.com/shop/edd-purchase-gravatars/
Description: Displays gravatars of customers who have purchased your product
Version: 1.0.1
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
Text Domain: edd-pg
Domain Path: languages
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Purchase_Gravatars' ) ) {

	class EDD_Purchase_Gravatars {

		private static $instance;

		/**
		 * Main Instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 *
		 */
		public static function instance() {
			if ( ! isset ( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		/**
		 * Start your engines
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function __construct() {
			$this->setup_actions();
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_actions() {
			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'widgets_init',  array( $this, 'register_widget' ) );
			add_shortcode( 'edd_purchase_gravatars', array( $this, 'shortcode' ) );
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );

			do_action( 'edd_pg_setup_actions' );
		}

		/**
		 * Internationalization
		 *
		 * @since 1.0
		 */
		public function textdomain() {
			load_plugin_textdomain( 'edd-pg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Utility function to check if a gravatar exists for a given email or id
		 * @param int|string|object $id_or_email A user ID, email address, or comment object
		 * @return bool if the gravatar exists or not
		 */

		// https://gist.github.com/justinph/5197810
		public function validate_gravatar( $id_or_email ) {
		  //id or email code borrowed from wp-includes/pluggable.php
			$email = '';
			if ( is_numeric( $id_or_email ) ) {
				$id = (int) $id_or_email;
				$user = get_userdata( $id );
				if ( $user )
					$email = $user->user_email;
			} elseif ( is_object( $id_or_email ) ) {
				// No avatar for pingbacks or trackbacks
				$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
				if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
					return false;

				if ( !empty( $id_or_email->user_id ) ) {
					$id = (int) $id_or_email->user_id;
					$user = get_userdata( $id );
					if ( $user )
						$email = $user->user_email;
				} elseif ( !empty( $id_or_email->comment_author_email ) ) {
					$email = $id_or_email->comment_author_email;
				}
			} else {
				$email = $id_or_email;
			}

			$hashkey = md5( strtolower( trim( $email ) ) );
			$uri = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

			$data = wp_cache_get( $hashkey );
			if ( false === $data ) {
				$response = wp_remote_head( $uri );
				if( is_wp_error( $response ) ) {
					$data = 'not200';
				} else {
					$data = $response['response']['code'];
				}
			    wp_cache_set( $hashkey, $data, $group = '', $expire = 60*5 );

			}
			if ( $data == '200' ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get an array of all the log IDs using the EDD Logging Class
		 *
		 * @since 1.0
		 * @param $download_id Download's ID
		 * @return array $log_ids
		*/
		public function get_log_ids( $download_id = '' ) {

			// Instantiate a new instance of the class
			$edd_logging = new EDD_Logging;

			// get log for this download
			$logs = $edd_logging->get_logs( $download_id, 'sale' );

			// if logs exist
			if ( $logs ) {
				// create array to store our log IDs into
				$log_ids = array();
				// add each log ID to the array
				foreach ( $logs as $log ) {
					$log_ids[] = $log->ID;
				}
				// return our array
				return $log_ids;
			}

			return null;

		}


		/**
		 * Get array of unique payment IDs
		 *
		 * @since 1.0
		 * @param int $download_id Download ID
		 * @return array $unique_ids
		*/
		public function get_payment_ids( $download_id = '' ) {

			global $edd_options;

			$log_ids = $this->get_log_ids( $download_id );

			if ( $log_ids ) {

				$payment_ids = array();

				foreach ( $log_ids as $id ) {
					// get the payment ID for each corresponding log ID
					$payment_ids[] = get_post_meta( $id, '_edd_log_payment_id', true );
				}

				// remove customers who have purchased more than once so we can have unique gravatar imagesw
				$unique_emails = array();

				foreach ( $payment_ids as $key => $id ) {

					$email = get_post_meta( $id, '_edd_payment_user_email', true );

					if ( isset ( $edd_options['edd_pg_has_gravatar_account'] ) ) {
						if ( ! $this->validate_gravatar( $email ) ) {
							continue;
						}
					}

					$unique_emails[$id] = get_post_meta( $id, '_edd_payment_user_email', true );

				}

				// strip duplicate emails
				$unique_emails = array_unique( $unique_emails );

				// convert the unique IDs back into simple array
				foreach ( $unique_emails as $id => $email ) {
					$unique_ids[] = $id;
				}

				// randomize the payment IDs if enabled
				if ( isset( $edd_options['edd_pg_random_gravatars'] ) ) {
					shuffle( $unique_ids );
				}

				// return our unique IDs
				return $unique_ids;

			}

		}


		/**
		 * Gravatars
		 *
		 * @since 1.0
		*/
		public function gravatars( $download_id = false, $title = '' ) {

			// unique $payment_ids
			$payment_ids = $this->get_payment_ids( $download_id );

			//	var_dump( $payment_ids );
			//	 var_dump( $this->get_log_ids( get_the_ID() ) );

			global $edd_options;

			// return if no ID
			if ( ! $download_id )
				return;

			// minimum amount of purchases before showing gravatars
			// if the number of items in array is not greater or equal to the number specified, then exit
			if ( isset( $edd_options['edd_pg_min_purchases_required'] ) && '' != $edd_options['edd_pg_min_purchases_required'] ) {
				if ( ! ( count( $payment_ids ) >= $edd_options['edd_pg_min_purchases_required'] ) )
					return;
			}

			ob_start();

			echo '<div class="edd-purchase-gravatars">';

			if ( isset ( $title ) ) {

				if ( $title ) {
					echo apply_filters( 'edd_pg_title', '<h2>' . esc_attr( $title ) .'</h2>' );
				}
				elseif ( isset( $edd_options['edd_pg_heading'] ) ) {
					echo apply_filters( 'edd_pg_title', '<h2>' . esc_attr( $edd_options['edd_pg_heading'] ) .'</h2>' );
				}

			}

			$i = 0;

			if ( $payment_ids ) {
				foreach ( $payment_ids as $id ) {

					// EDD saves a blank option even when the control is turned off, hence the extra check
					if ( isset( $edd_options['edd_pg_maximum_number'] ) && '' != $edd_options['edd_pg_maximum_number'] && $i == $edd_options['edd_pg_maximum_number'] )
						continue;

					// get the payment meta
					$payment_meta = get_post_meta( $id, '_edd_payment_meta', true );

					// unserialize the payment meta
					$user_info = maybe_unserialize( $payment_meta['user_info'] );

					// get customer's first name
					$name = apply_filters( 'edd_pg_name', $user_info['first_name'] );

					// get customer's email
					$email = get_post_meta( $id, '_edd_payment_user_email', true );

					// set gravatar size and provide filter
					$size = isset( $edd_options['edd_pg_gravatar_size'] ) ? apply_filters( 'edd_pg_gravatar_size', $edd_options['edd_pg_gravatar_size'] ) : '';

					// default image
					$default_image = apply_filters( 'edd_pg_gravatar_default_image', false );

					echo '<span class="edd-purchase-gravatar">';

					// show gravatar
					echo get_avatar( $email, $size, $default_image, $name );

					do_action( 'edd_purchase_gravatars' );

					echo '</span>';

				 	$i++;

				} // end foreach
			}

			echo '</div>';

			?>

		<?php
			return apply_filters( 'edd_pg_gravatars', ob_get_clean() );
		}

		/**
		 * Register widget
		 *
		 * @since 1.0
		*/
		public function register_widget() {
			register_widget( 'EDD_Purchase_Gravatars_Widget' );
		}

		/**
		 * Shortcode
		 *
		 * @since 1.0
		 * @todo set the ID to get_the_ID() if ID parameter is not passed through. Otherwise it will incorrectly get other gravatars
		*/
		public function shortcode( $atts, $content = null ) {

			extract( shortcode_atts( array(
					'id' => '',
					'title' => ''
				), $atts, 'edd_purchase_gravatars' )
			);

			// if no ID is passed on single download pags, get the correct ID
			if ( is_singular( 'download' ) )
				$id = get_the_ID();

			$content = $this->gravatars( $id, $title );

			return $content;

		}

		/**
		 * Settings
		 *
		 * @since 1.0
		*/
		public function settings( $settings ) {

		  $edd_pg_settings = array(
				array(
					'id' => 'edd_pg_header',
					'name' => '<strong>' . __( 'Purchase Gravatars', 'edd-pg' ) . '</strong>',
					'type' => 'header'
				),

				array(
					'id' => 'edd_pg_heading',
					'name' => __( 'Heading', 'edd-pg' ),
					'desc' => __( 'The heading to display above the Gravatars', 'edd-pg' ),
					'type' => 'text',
					'std' => ''
				),
				array(
					'id' => 'edd_pg_gravatar_size',
					'name' => __( 'Gravatar Size', 'edd-pg' ),
					'desc' => __( 'The size of each Gravatar in pixels (512px maximum)', 'edd-pg' ),
					'type' => 'text',
					'std' => '48'
				),
				array(
					'id' => 'edd_pg_min_purchases_required',
					'name' => __( 'Minimum Unique Purchases Required', 'edd-pg' ),
					'desc' => sprintf( __( 'The minimum number of unique purchases a %s must have before the Gravatars are shown. Leave blank for no minimum.', 'edd-pg' ), strtolower( edd_get_label_singular() ) ),
					'type' => 'text',
					'std' => ''
				),
				array(
					'id' => 'edd_pg_maximum_number',
					'name' => __( 'Maximum Gravatars To Show', 'edd-pg' ),
					'desc' => __( 'The maximum number of gravatars to show. Leave blank for no limit.', 'edd-pg' ),
					'type' => 'text',
					'std' => ''
				),
				array(
					'id' => 'edd_pg_has_gravatar_account',
					'name' => __( 'Gravatar Visibility', 'edd-pg' ),
					'desc' => __( 'Only show customers with a Gravatar account', 'edd-pg' ),
					'type' => 'checkbox',
				),
				array(
					'id' => 'edd_pg_random_gravatars',
					'name' => __( 'Randomize Gravatars', 'edd-pg' ),
					'desc' => __( 'Randomize the Gravatars', 'edd-pg' ),
					'type' => 'checkbox',
				),
			);

			return array_merge( $settings, $edd_pg_settings );
		}

	}

}


/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */
function edd_purchase_gravatars_load() {
	$edd_purchase_gravatars = new EDD_Purchase_Gravatars();
}
add_action( 'plugins_loaded', 'edd_purchase_gravatars_load' );

/**
 * Widget
 *
 * @since 1.0
*/

if ( ! class_exists( 'EDD_Purchase_Gravatars_Widget' ) ) {

	class EDD_Purchase_Gravatars_Widget extends WP_Widget {

	    /*
	     * widget constructor
	     */
	    function edd_purchase_gravatars_widget() {

	    	$edd_label_singular = function_exists('edd_get_label_singular') ? strtolower( edd_get_label_singular() ) : null;

	        // widget settings
	        $widget_ops = array(
	            'classname' => 'purchase-gravatars',
	            'description' => sprintf( __( 'Displays gravatars of customers who have purchased your %s. Will only show on the single %s page.', 'edd-pg' ), $edd_label_singular, $edd_label_singular )
	        );

	        // widget control settings
	        $control_ops = array(
	            'width' => 250,
	            'height' => 350,
	            'id_base' => 'edd_pg_widget'
	        );

	        // create the widget
			parent::__construct( 'edd_pg_widget', __( 'EDD Purchase Gravatars', 'edd-pg' ), $widget_ops, $control_ops );

	    } // end constructor

	    /*
	     * Outputs the content of the widget
	     */
	    function widget( $args, $instance ) {
	    	global $edd_options;

	        extract( $args );

	        if ( ! is_singular( 'download' ) )
	        	return;

	        // Variables from widget settings
	        $title = apply_filters( 'widget_title', $instance['title'] );

	        // Used by themes. Opens the widget
	        echo $before_widget;

	        // Display the widget title
	        if ( $title )
	            echo $before_title . $title . $after_title;

	        $gravatars = new EDD_Purchase_Gravatars();

			echo $gravatars->gravatars( get_the_ID(), null ); // remove title

	        // Used by themes. Closes the widget
	        echo $after_widget;

	    } // end WIDGET function

	    /*
	     * Update function. Processes widget options to be saved
	     */
	    function update( $new_instance, $old_instance ) {

	        $instance = $old_instance;

	        $instance['title'] = strip_tags( $new_instance['title'] );

	        return $instance;

	    } // end UPDATE function

	    /*
	     * Form function. Displays the actual form on the widget page
	     */
	    function form( $instance ) {

	        // Set up some default widget settings.
	        $defaults = array(
	            'title' => '',
	        );

	        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

	        <!-- Title -->
	        <p>
	            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'edd-pg' ) ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
	        </p>


	    <?php } // end FORM function

	}
}
