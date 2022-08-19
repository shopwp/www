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
		add_filter( 'edd_cr_user_can_access_status_and_message', array( $this, 'can_access_content' ), 10, 4 );

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

		add_action( 'edd_cr_restricted_table_before', array( $this, 'metabox' ), 10, 1 );
		add_action( 'edd_cr_metabox', array( $this, 'deprecated_metabox' ), 10, 3 );
		add_action( 'edd_cr_save_meta_data', array( $this, 'save_data' ), 10, 2 );
	}


	/**
	 * Attach our extra meta box field
	 *
	 * @since  1.0
	 * @return void
	 */

	public function metabox( $post_id ) {

			$active_only = get_post_meta( $post_id, '_edd_cr_active_only', true );

			echo '<p>';
				echo '<label for="edd_cr_active_only" title="' . __( 'Only customers with an active recurring subscription will be able to view the content.', 'edd-recurring' ) . '">';
					echo '<input type="checkbox" name="edd_cr_active_only" id="edd_cr_active_only" value="1"' . checked( '1', $active_only, false ) . '/>&nbsp;';
					echo __( 'Active Subscribers Only?', 'edd-recurring' );
				echo '</label>';
			echo '</p>';

	}

	/**
	 * For backwards compatibility only, this function remains, and is renamed to deprecated_metabox instead of just metabox.
	 * For the correct/current usage, see the metabox method in this EDD_Recurring_Content_Restriction class,
	 * and the edd_cr_restricted_table_before hook added in Content Restriction version 2.3
	 * Attach our extra meta box field
	 *
	 * @since  2.8
	 * @return void
	 */

	public function deprecated_metabox( $post_id, $restricted_to, $restricted_variable ) {

		// If the newer hook has been run, don't run this deprecated function
		if ( did_action( 'edd_cr_restricted_table_before' ) ) {
			return;
		}

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

		if ( ! edd_cr_is_restricted( $post_id ) ) {
			return $is_restricted;
		}

		if ( ! get_post_meta( $post_id, '_edd_cr_active_only', true ) ) {
			return $is_restricted; // Leave untouched
		}

		// Check if the product is a variably-priced product.
		if ( $price_id ) {
			// Check if the variably-riced product is Recurring-enabled or not
			$is_recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
		} else {
			// Check if the product is Recurring-enabled or not
			$is_recurring = EDD_Recurring()->is_recurring( $download_id );
		}

		if ( ! $is_recurring ) {

			// If this product is not Recurring-enabled, return the boolean untouched
			return $is_restricted;

		}


		$subscriber = new EDD_Recurring_Subscriber( $user_id, true );

		if ( ! $subscriber->has_active_product_subscription( $post_id ) ) {
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
	public function can_access_content( $return, $user_id, $restricted_to, $post_id ) {

		$has_access = $return['status'];

		if ( ! is_array( $restricted_to ) ) {
			return $return;
		}

		$active_only  = get_post_meta( get_the_ID(), '_edd_cr_active_only', true );
		$subscriber   = new EDD_Recurring_Subscriber( $user_id, true );

		if ( $active_only ) {

			$has_access = false;

			foreach ( $restricted_to as $item ) {

				if ( 'any' === $item['download'] ) {
					$return['status'] = $subscriber->has_active_subscription() ? true : false;

					return $return;
				}

				// Get the Download object so we can use it in variable price checks.
				$download = new EDD_Download( $item['download'] );

				if ( isset( $item['price_id'] ) && $download->has_variable_prices() ) {
					if ( is_numeric( $item['price_id'] ) ) {

						// Check if the variably-riced product is Recurring-enabled or not.
						$recurring_enabled = EDD_Recurring()->is_price_recurring( $item['download'], $item['price_id'] );

					} elseif ( 'all' === $item['price_id'] ) {

						foreach ( $download->get_prices() as $price ) {

							if ( 'yes' === strtolower( $price['recurring'] ) ) {
								$recurring_enabled = true;
								break;
							}

						}

					}

				} else {

					// Check if the product is Recurring-enabled or not.
					$recurring_enabled = EDD_Recurring()->is_recurring( $item['download'] );

				}

				if ( $recurring_enabled ) {

					// If this subscriber has an active subscription to the variably-product in question.
					if ( $subscriber->has_active_product_subscription( $item['download'] ) ) {
						$has_access = true;
						break;
					}

				} else {

					// The edd_cr_user_can_access_with_purchase was introduced in version 2.3 of Content Restriction, so add a check for it.
					if ( function_exists( 'edd_cr_user_can_access_with_purchase' ) ) {

						// If this is a non-recurring product, re-check it to see if they have access because of it
						$has_access_because_of_non_recurring = edd_cr_user_can_access_with_purchase( $user_id, array( $item ), $post_id );

						if ( $has_access_because_of_non_recurring['status'] ) {
							$has_access = true;
							break;
						}

					} else {

						// If the edd_cr_user_can_access_with_purchase function does not exist (because Content Restriction is older than 2.3)
						$has_access = false;
						break;

					}

				}
			}
		}

		$return['status'] = $has_access;

		return $return;
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
	 * @param  string $content       The content between the shortcode tags.
	 * @param  array  $restricted_to The list of items to restrict to.
	 * @param  array  $atts          The array of attributes.
	 * @return string                The new content
	 */
	public function restrict_shortcode_content( $content, $restricted_to, $atts ) {
		$user_id = get_current_user_id();
		if ( empty( $user_id ) || true !== filter_var( $atts['subscription'], FILTER_VALIDATE_BOOLEAN ) ) {
			return $content;
		}

		// Check if the content is available for any active subscription.
		$subscriber = new EDD_Recurring_Subscriber( $user_id, true );
		if ( 'any' === $atts['id'] && $subscriber->has_active_subscription() ) {
			return $content;
		}

		$has_access     = false;
		$message        = edd_cr_get_any_restriction_message();
		$custom_message = isset( $atts['message'] ) ? $atts['message'] : false;
		$products       = array();
		foreach ( $restricted_to as $item ) {
			if ( edd_recurring()->is_recurring( $item['download'] ) ) {
				$has_access = $subscriber->has_active_product_subscription( $item['download'] );
			} else {
				if ( ! empty( $item['download']['price_id'] ) && is_numeric( $item['download']['price_id'] ) && edd_has_variable_prices( $item['download'] ) ) {
					$has_access = edd_has_user_purchased( $user_id, $item['download'], $item['download']['price_id'] );
				} else {
					$has_access = edd_has_user_purchased( $user_id, $item['download'] );
				}
			}
			if ( $has_access ) {
				return $content;
			}
			$products[] = get_the_title( $item['download'] );
		}

		// At this point, $has_access is false and we just need to get the correct message.
		if ( ! $custom_message && ! empty( $products ) ) {
			$count = count( $products );
			if ( $count > 1 ) {
				$message      = edd_cr_get_multi_restriction_message();
				$product_list = '<ul>';
				foreach ( $products as $product ) {
					$product_list .= '<li>' . $product . '</li>';
				}
				$product_list .= '</ul>';
				$message       = str_replace( '{product_names}', $product_list, $message );
			} else {
				$message = edd_cr_get_single_restriction_message();
				$message = str_replace( '{product_name}', reset( $products ), $message );
			}
		}

		// phpcs:ignore WordPress.PHP.DisallowShortTernary
		return $custom_message ?: $message;
	}

}
