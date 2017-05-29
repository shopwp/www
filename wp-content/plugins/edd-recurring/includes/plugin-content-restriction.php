<?php


/**
 * Integrates EDD Recurring with the Content Restriction extension
 *
 * This allows content to be restricted to active subscribers only
 *
 * @since v1.0
 */

class EDD_Recurring_Content_Restriction {


	/**
	 * Get things started
	 *
	 * @since  1.0
	 * @return void
	 */

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Pre 2.0 filter
		add_filter( 'edd_cr_is_restricted', array( $this, 'restrict' ), 10, 5 );

		// 2.0+ filter
		add_filter( 'edd_cr_user_can_access', array( $this, 'can_access_content' ), 10, 3 );

		add_filter( 'shortcode_atts_edd_restrict', array( $this, 'restrict_shortcode_atts' ), 10, 3 );
		add_filter( 'edd_cr_restrict_shortcode_content', array( $this, 'restrict_shortcode_content' ), 10, 3 );
	}


	/**
	 * Load our admin actions
	 *
	 * @since  1.0
	 * @return void
	 */

	public function admin_init() {

		if( ! class_exists( 'EDD_Content_Restriction' ) ) {
			return; // Content Restriction extension not active
		}

		add_action( 'edd_cr_metabox', array( $this, 'metabox' ), 10, 3 );
		add_action( 'edd_cr_save_meta_data', array( $this, 'save_data' ), 10, 2 );
	}


	/**
	 * Attach our extra meta box field
	 *
	 * @since  1.0
	 * @return void
	 */

	public function metabox( $post_id, $restricted_to, $restricted_variable ) {

		static $cr_active_only;

		if( empty( $cr_active_only ) ) {

			$active_only = get_post_meta( $post_id, '_edd_cr_active_only', true );
			echo '<p>';
				echo '<label for="edd_cr_active_only" title="' . __( 'Only customers with an active recurring subscription will be able to view the content.', 'edd-recurring' ) . '">';
					echo '<input type="checkbox" name="edd_cr_active_only" id="edd_cr_active_only" value="1"' . checked( '1', $active_only, false ) . '/>&nbsp;';
					echo __( 'Active Subscribers Only?', 'edd-recurring' );
				echo '</label>';
			echo '</p>';

		}

		$cr_active_only = true;
	}


	/**
	 * Save data from the meta box
	 *
	 * @since  1.0
	 * @return void
	 */


	public function save_data( $post_id, $data ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if( isset( $data['edd_cr_active_only'] ) ) {
			update_post_meta( $post_id, '_edd_cr_active_only', '1' );
		} else {
			delete_post_meta( $post_id, '_edd_cr_active_only' );
		}
	}


	/**
	 * Check if user has access to content
	 *
	 * @since  1.0
	 * @return bool
	 */
	public function restrict( $is_restricted = false, $post_id = 0, $download_id = 0, $user_id = 0, $price_id = null ) {

		if( ! edd_cr_is_restricted( $post_id ) ) {
			return $is_restricted;
		}

		if( ! get_post_meta( $post_id, '_edd_cr_active_only', true ) ) {
			return $is_restricted; // Leave untouched
		}


		$subscriber = new EDD_Recurring_Subscriber( $user_id, true );

		if( ! $subscriber->has_active_product_subscription( $post_id ) ) {
			return true;
		}

		return $is_restricted;
	}

	/**
	 * Check if user has access to content
	 *
	 * @since  2.2.7
	 * @return bool
	 */
	public function can_access_content( $has_access, $user_id, $restricted_to ) {

		if( $has_access && is_array( $restricted_to ) ) {

			$active_only  = get_post_meta( get_the_ID(), '_edd_cr_active_only', true );
			$subscriber   = new EDD_Recurring_Subscriber( $user_id, true );
			$download_ids = wp_list_pluck( $restricted_to, 'download' );

			if( $active_only ) {

				if ( in_array( 'any', $download_ids ) ) {

					if ( $active_only && ! $subscriber->has_active_subscription() ) {
						$has_access = false;
					}

				} else {

					$has_access = false;

					foreach( $restricted_to as $item ) {

						if( $subscriber->has_active_product_subscription( $item['download'] ) ) {
							$has_access = true;
							break;
						}

					}

				}

			}

		}

		return $has_access;
	}

	/**
	 * Sets the active subscription restriction on the edd_restrict shortcode by default and allows overriding it
	 *
	 * @since  2.4
	 * @param  array $out   The attributes to return
	 * @param  array $pairs Attribute pairs
	 * @param  array $atts  Passed attributes
	 * @return array
	 */
	public function restrict_shortcode_atts( $out, $pairs, $atts ) {

		if( ! isset( $atts['subscription'] ) ) {
			$out['subscription'] = false;
		} else {
			foreach ( $atts as $key => $value ) {
				if( false !== strpos( $key, 'subscription' ) ) {
					$out['subscription'] = $value;
				}
			}
		}

		return $out;

	}

	/**
	 * Allows subscriptions to modify the edd_restrict shortcode
	 *
	 * @since  2.4
	 * @param  string $content       The content between the shortcode tags
	 * @param  array  $restricted_to The list of items to restrict to
	 * @param  array  $atts          The array of attributes
	 * @return string                The new content
	 */
	public function restrict_shortcode_content( $content, $restricted_to, $atts ) {
		global $user_ID;
		$has_access = false;

		if ( ! empty( $user_ID ) && true === filter_var( $atts['subscription'], FILTER_VALIDATE_BOOLEAN ) ) {

			$subscriber = new EDD_Recurring_Subscriber( $user_ID, true );

			if ( 'any' === $atts['id'] && $subscriber->has_active_subscription() ) {

				$has_access = true;

			} else {

				foreach( $restricted_to as $item ) {

					if( $subscriber->has_active_product_subscription( $item['download'] ) ) {
						$has_access = true;
						break;
					}

				}

			}

			if( $has_access == false ) {
				$content = __( 'This content is restricted to buyers.', 'edd-cr' );
			}

		}

		return $content;

	}

}
