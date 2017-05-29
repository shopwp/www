<?php

/**
 * Load the admin javascript
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_admin_scripts( $hook ) {
	global $post, $edd_recurring;

	if ( ! is_object( $post ) && 'download_page_edd-subscriptions' !== $hook ) {
		return;
	}

	if ( is_object( $post ) && 'download' != $post->post_type ) {
		return;
	}

	$pages = array( 'post.php', 'post-new.php', 'download_page_edd-subscriptions' );

	if ( ! in_array( $hook, $pages ) ) {
		return;
	}

	wp_register_script( 'edd-admin-recurring', EDD_Recurring::$plugin_dir . '/assets/js/edd-admin-recurring.js', array('jquery'));
	wp_enqueue_script( 'edd-admin-recurring' );

	$ajax_vars = array(
		'singular'            => _x( 'time', 'Referring to billing period', 'edd-recurring' ),
		'plural'              => _x( 'times', 'Referring to billing period', 'edd-recurring' ),
		'enabled_gateways'    => edd_get_enabled_payment_gateways(),
		'invalid_time'        => array(
			'paypal'          => __( 'PayPal Standard requires recurring times to be set to 0 for indefinite subscriptions or a minimum value of 2 and a maximum value of 52 for limited subscriptions.', 'edd-recurring' ),
		),
		'delete_subscription' => __( 'Are you sure you want to delete this subscription?', 'edd-recurring' ),
		'action_edit'         => __( 'Edit', 'edd-recurring' ),
		'action_cancel'       => __( 'Cancel', 'edd-recurring' ),
	);

	wp_localize_script( 'edd-admin-recurring', 'EDD_Recurring_Vars', $ajax_vars );
}

add_action( 'admin_enqueue_scripts', 'edd_recurring_admin_scripts' );
