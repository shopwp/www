<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Variable Prices
|--------------------------------------------------------------------------
*/


/**
 * Meta box table header
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_head( $download_id ) {
	?>
	<th><?php _e( 'Recurring', 'edd-recurring' ); ?></th>
	<th><?php _e( 'Period', 'edd-recurring' ); ?></th>
	<th><?php echo _x( 'Times', 'Referring to billing period', 'edd-recurring' ); ?></th>
	<th><?php echo _x( 'Signup Fee', 'Referring to subscription signup fee', 'edd-recurring' ); ?></th>
	<?php
}
add_action( 'edd_download_price_table_head', 'edd_recurring_metabox_head', 999 );

/**
 * Add a hook to the variable price rows that all of our other fields can hook into
 *
 * @access      public
 * @since       2.6
 * @return      void
 */
function edd_recurring_price_row_hook( $download_id, $price_id, $args  ) {
	do_action( 'edd_recurring_download_price_row', $download_id, $price_id, $args );
}
add_action( 'edd_download_price_table_row', 'edd_recurring_price_row_hook', 999, 3 );


/**
 * Meta box is recurring yes/no field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_recurring( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );

	?>
	<td class="edd-recurring-enabled">
		<select name="edd_variable_prices[<?php echo $price_id; ?>][recurring]" id="edd_variable_prices[<?php echo $price_id; ?>][recurring]">
			<option value="no" <?php selected( $recurring, false ); ?>><?php echo esc_attr_e( 'No', 'edd-recurring' ); ?></option>
			<option value="yes" <?php selected( $recurring, true ); ?>><?php echo esc_attr_e( 'Yes', 'edd-recurring' ); ?></option>
		</select>
	</td>
	<?php
}
add_action( 'edd_recurring_download_price_row', 'edd_recurring_metabox_recurring', 999, 3 );


/**
 * Meta box recurring period field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_period( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$periods   = EDD_Recurring()->periods();
	$period    = EDD_Recurring()->get_period( $price_id );

	$disabled = $recurring ? '' : 'disabled="disabled" ';

	?>
	<td class="edd-recurring-period">
		<select <?php echo $disabled; ?>name="edd_variable_prices[<?php echo $price_id; ?>][period]" id="edd_variable_prices[<?php echo $price_id; ?>][period]">
			<?php foreach ( $periods as $key => $value ) : ?>
				<option value="<?php echo $key; ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
	<?php
}
add_action( 'edd_recurring_download_price_row', 'edd_recurring_metabox_period', 999, 3 );


/**
 * Meta box recurring times field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_times( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$times     = EDD_Recurring()->get_times( $price_id );
	$period    = EDD_Recurring()->get_period( $price_id );

	$disabled = $recurring ? '' : 'disabled="disabled" ';

	?>
	<td class="times">
		<input <?php echo $disabled; ?>type="number" min="0" step="1" name="edd_variable_prices[<?php echo $price_id; ?>][times]" id="edd_variable_prices[<?php echo $price_id; ?>][times]" size="4" style="width: 40px" value="<?php echo $times; ?>" />
	</td>
	<?php
}
add_action( 'edd_recurring_download_price_row', 'edd_recurring_metabox_times', 999, 3 );


/**
 * Meta box recurring fee field
 *
 * @access      public
 * @since       1.1
 * @return      void
 */
function edd_recurring_metabox_signup_fee( $download_id, $price_id, $args ) {

	$recurring  = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$has_trial  = EDD_Recurring()->has_free_trial( $download_id );
	$signup_fee = EDD_Recurring()->get_signup_fee( $price_id, $download_id );

	$disabled = $recurring && ! $has_trial ? '' : 'disabled="disabled" ';

	?>
	<td class="signup_fee">
		<input <?php echo $disabled; ?>type="number" step="0.01" name="edd_variable_prices[<?php echo $price_id; ?>][signup_fee]" id="edd_variable_prices[<?php echo $price_id; ?>][signup_fee]" size="4" style="width: 60px" value="<?php echo $signup_fee; ?>" />
	</td>
	<?php
}
add_action( 'edd_recurring_download_price_row', 'edd_recurring_metabox_signup_fee', 999, 3 );


/**
 * Meta fields for EDD to save
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edd_recurring_save_single( $fields ) {
	$fields[] = 'edd_period';
	$fields[] = 'edd_times';
	$fields[] = 'edd_recurring';
	$fields[] = 'edd_signup_fee';

	if( defined( 'EDD_CUSTOM_PRICES' ) ) {
		$fields[] = 'edd_custom_signup_fee';
		$fields[] = 'edd_custom_recurring';
		$fields[] = 'edd_custom_times';
		$fields[] = 'edd_custom_period';
	}

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_recurring_save_single' );

/**
 * Store the trial options
 *
 * @access      public
 * @since       2.6
 * @return      void
 */
function edd_recurring_save_trial_period( $post_id, $post ) {

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if( ! empty( $_POST['edd_recurring_free_trial'] ) ) {

		$default = array(
			'quantity' => 1,
			'unit'     => 'month',
		);

		$period             = array();
		$period['unit']     = sanitize_text_field( $_POST['edd_recurring_trial_unit'] );
		$period['quantity'] = absint( $_POST['edd_recurring_trial_quantity'] );
		$period             = wp_parse_args( $period, $default );

		update_post_meta( $post_id, 'edd_trial_period', $period );

	} else {

		delete_post_meta( $post_id, 'edd_trial_period' );

	}
}
add_action( 'edd_save_download', 'edd_recurring_save_trial_period', 10, 2 );


/**
 * Set colspan on submit row
 *
 * This is a little hacky, but it's the best way to adjust the colspan on the submit row to make sure it goes full width
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_colspan() {
	echo '<script type="text/javascript">jQuery(function($){ $("#edd_price_fields td.submit").attr("colspan", 7)});</script>';
}
add_action( 'edd_meta_box_fields', 'edd_recurring_metabox_colspan', 20 );


/*
|--------------------------------------------------------------------------
| Single Price Options
|--------------------------------------------------------------------------
*/

/**
 * Add a hook to the Prices metabox that all of our other fields can hook into
 *
 * @access      public
 * @since       2.6
 * @return      void
 */
function edd_recurring_metabox_hook( $download_id ) {
	do_action( 'edd_recurring_download_metabox', $download_id );
}
add_action( 'edd_price_field', 'edd_recurring_metabox_hook', 10 );


/**
 * Meta box is recurring yes/no field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_single_recurring( $download_id ) {

	$recurring = EDD_Recurring()->is_recurring( $download_id );

	?>
	<label><?php _e( 'Recurring', 'edd-recurring' ); ?></label>
	<select name="edd_recurring" id="edd_recurring">
		<option value="no" <?php selected( $recurring, false ); ?>><?php echo esc_attr_e( 'No', 'edd-recurring' ); ?></option>
		<option value="yes" <?php selected( $recurring, true ); ?>><?php echo esc_attr_e( 'Yes', 'edd-recurring' ); ?></option>
	</select>
	<?php
}
add_action( 'edd_recurring_download_metabox', 'edd_recurring_metabox_single_recurring' );


/**
 * Meta box recurring period field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_single_period( $download_id ) {

	$periods = EDD_Recurring()->periods();
	$period  = EDD_Recurring()->get_period_single( $download_id );
	?>
	<label><?php _e( 'Period', 'edd-recurring' ); ?></label>
	<select name="edd_period" id="edd_period">
		<?php foreach ( $periods as $key => $value ) : ?>
			<option value="<?php echo $key; ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'edd_recurring_download_metabox', 'edd_recurring_metabox_single_period' );


/**
 * Meta box recurring times field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_recurring_metabox_single_times( $download_id ) {

	$times = EDD_Recurring()->get_times_single( $download_id );
	?>

	<span class="times">
		<label><?php _e( 'Times', 'edd-recurring' ); ?></label>
		<input type="number" min="0" step="1" name="edd_times" id="edd_times" size="4" style="width: 40px" value="<?php echo $times; ?>" />
	</span>
	<?php
}
add_action( 'edd_recurring_download_metabox', 'edd_recurring_metabox_single_times' );


/**
 * Meta box recurring signup fee field
 *
 * @access      public
 * @since       1.1
 * @return      void
 */
function edd_recurring_metabox_single_signup_fee( $download_id ) {

	$has_trial  = EDD_Recurring()->has_free_trial( $download_id );
	$signup_fee = EDD_Recurring()->get_signup_fee_single( $download_id );
	$disabled   = $has_trial ? ' disabled="disabled"' : '';
	?>

	<span class="signup_fee">
		<label><?php _e( 'Signup Fee', 'edd-recurring' ); ?></label>
		<input type="number" step="0.01" name="edd_signup_fee" id="edd_signup_fee" size="4" style="width: 60px" value="<?php echo $signup_fee; ?>"<?php echo $disabled;?>/>
	</span>
	<?php
}
add_action( 'edd_recurring_download_metabox', 'edd_recurring_metabox_single_signup_fee' );

/**
 * Free trial options
 *
 * @access      public
 * @since       2.6
 * @return      void
 */
function edd_recurring_metabox_trial_options( $download_id ) {

	$has_trial      = EDD_Recurring()->has_free_trial( $download_id );
	$periods        = EDD_Recurring()->singular_periods();
	$period         = EDD_Recurring()->get_trial_period( $download_id );
	$option_display = $has_trial ? '' : ' style="display:none;"';

	// Remove non-valid trial periods
	unset( $periods['quarter'] );
	unset( $periods['semi-year'] );

	$one_one_discount_help = '';
	if( edd_get_option( 'recurring_one_time_discounts' ) ) {
		$one_one_discount_help = ' ' . __( '<strong>Additional note</strong>: with free trials, one time discounts are not supported and discount codes for this product will apply to all payments after the trial period.', 'edd-recurring' );
	}

	?>
	<div id="edd_recurring_free_trial_options_wrap">

		<?php if( edd_is_gateway_active( '2checkout' ) || edd_is_gateway_active( '2checkout_onsite' ) ) : ?>
			<p><strong><?php _e( '2Checkout does not support free trial periods. Subscriptions purchased through 2Checkout cannot include free trials.', 'edd-recurring' ); ?></strong></p>
		<?php endif; ?>

		<p>
			<input type="checkbox" name="edd_recurring_free_trial" id="edd_recurring_free_trial" value="yes"<?php checked( true, $has_trial ); ?>/>
			<label for="edd_recurring_free_trial">
				<?php _e( 'Enable free trial for subscriptions', 'edd-recurring' ); ?>
				<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( 'Check this box to include a free trial with subscriptions for this product. When signing up for a free trial, the customer\'s payment details will be taken at checkout but the customer will not be charged until the free trial is completed. <strong>Note:</strong> this only applies when purchasing a subscription. If a price option is not set to recurring, this free trial will not be used.', 'edd-recurring' ); echo $one_one_discount_help; ?>"></span>
			</label>
		</p>
		<p id="edd_recurring_free_trial_options"<?php echo $option_display; ?>>
			<input name="edd_recurring_trial_quantity" id="edd_recurring_trial_quantity" type="number" min="1" step="1" style="width: 60px;" value="<?php echo esc_attr( $period['quantity'] ); ?>" placeholder="1"/>
			<select name="edd_recurring_trial_unit" id="edd_recurring_trial_unit">
				<?php foreach ( $periods as $key => $value ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( $period['unit'], $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
	<?php
}
add_action( 'edd_meta_box_price_fields', 'edd_recurring_metabox_trial_options' );

if( defined( 'EDD_CUSTOM_PRICES' ) ) {

	/**
	 * Recurring options for Custom Prices
	 *
	 * @access      public
	 * @since       2.5
	 * @return      void
	 */

	function edd_recurring_metabox_custom_options( $download_id ) {

		$custom     = get_post_meta( $download_id, '_edd_cp_custom_pricing', true );
		$recurring  = EDD_Recurring()->is_custom_recurring( $download_id );
		$periods    = EDD_Recurring()->periods();
		$period     = EDD_Recurring()->get_custom_period( $download_id );
		$times      = EDD_Recurring()->get_custom_times( $download_id );
		$signup_fee = EDD_Recurring()->get_custom_signup_fee( $download_id );
		$display    = $custom ? '' : ' style="display:none;"';
		?>
		<div class="edd_recurring_custom_wrap"<?php echo $display; ?>>
			<p><strong><?php _e( 'Recurring Options for Custom Prices', 'edd-recurring' ); ?></strong></p>
			<p><?php _e( 'Select the recurring options for customers that pay with a custom price.', 'edd-recurring' ); ?></p>
			<label><?php _e( 'Recurring', 'edd-recurring' ); ?></label>
			<select name="edd_custom_recurring" id="edd_custom_recurring">
				<option value="no" <?php selected( $recurring, false ); ?>><?php echo esc_attr_e( 'No', 'edd-recurring' ); ?></option>
				<option value="yes" <?php selected( $recurring, true ); ?>><?php echo esc_attr_e( 'Yes', 'edd-recurring' ); ?></option>
			</select>
			<label><?php _e( 'Period', 'edd-recurring' ); ?></label>
			<select name="edd_custom_period" id="edd_custom_period">
				<?php foreach ( $periods as $key => $value ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="times">
				<label><?php _e( 'Times', 'edd-recurring' ); ?></label>
				<input type="number" min="0" step="1" name="edd_custom_times" id="edd_times" size="4" style="width: 40px" value="<?php echo $times; ?>" />
			</span>
			<span class="signup_fee">
				<label><?php _e( 'Signup Fee', 'edd-recurring' ); ?></label>
				<input type="number" step="0.01" name="edd_custom_signup_fee" id="edd_signup_fee" size="4" style="width: 60px" value="<?php echo $signup_fee; ?>" />
			</span>
			<hr/>
		</div><!--close .edd_recurring_custom_wrap-->
		<?php
	}
	add_action( 'edd_after_price_field', 'edd_recurring_metabox_custom_options', 10 );

}

/**
 * Display Subscription Payment Notice
 *
 * @description Adds a subscription payment indicator within the single payment view "Update Payment" metabox (top)
 * @since       2.4
 *
 * @param $payment_id
 *
 */
function edd_display_subscription_payment_meta( $payment_id ) {

	$is_sub = edd_get_payment_meta( $payment_id, '_edd_subscription_payment' );

	if ( $is_sub ) :
		$subs_db = new EDD_Subscriptions_DB;
		$subs    = $subs_db->get_subscriptions( array( 'parent_payment_id' => $payment_id, 'order' => 'ASC' ) );
?>
		<div id="edd-order-subscriptions" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Subscriptions', 'edd-recurring' ); ?></span>
			</h3>
			<div class="inside">

				<?php foreach( $subs as $sub ) : ?>
					<?php $sub_url = admin_url( 'edit.php?post_type=download&page=edd-subscriptions&id=' . $sub->id ); ?>
					<p>
						<span class="label"><span class="dashicons dashicons-update"></span> <?php printf( __( 'Subscription ID: <a href="%s">#%d</a>', 'edd_recurring' ), $sub_url, $sub->id ); ?></span>&nbsp;
					</p>
					<?php $payments = $sub->get_child_payments(); ?>
					<?php if( $payments ) : ?>
						<p><strong><?php _e( 'Renewal Payments:', 'edd-recurring' ); ?></strong></p>
						<ul id="edd-recurring-sub-payments">
						<?php foreach( $payments as $payment ) : ?>
							<li>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID ) ); ?>">
									<?php if( function_exists( 'edd_get_payment_number' ) ) : ?>
										<?php echo '#' . edd_get_payment_number( $payment->ID ); ?>
									<?php else : ?>
										<?php echo '#' . $payment->ID; ?>
									<?php endif; ?>&nbsp;&ndash;&nbsp;
								</a>
								<span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?>&nbsp;&ndash;&nbsp;</span>
								<span><?php echo edd_payment_amount( $payment->ID ); ?></span>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
<?php
	endif;
}
add_action( 'edd_view_order_details_sidebar_before', 'edd_display_subscription_payment_meta', 10, 1 );

/**
 * List subscription (sub) payments of a particular parent payment
 *
 * The parent payment ID is the very first payment made. All payments made after for the profile are sub.
 *
 * @since  1.0
 * @return void
 */
function edd_recurring_display_parent_payment( $payment_id = 0 ) {

	$payment = new EDD_Payment( $payment_id );

	if( $payment->parent_payment ) :

		$parent_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->parent_payment );
?>
		<div id="edd-order-subscription-payments" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Subscription', 'edd-recurring' ); ?></span>
			</h3>
			<div class="inside">
				<p><?php printf( __( 'Parent Payment: <a href="%s">%s</a>' ), $parent_url, $payment->number ); ?></p>
			</div><!-- /.inside -->
		</div><!-- /#edd-order-subscription-payments -->
<?php
	endif;
}
add_action( 'edd_view_order_details_sidebar_before', 'edd_recurring_display_parent_payment', 10 );

/**
 * Display Subscription transaction IDs for parent payments
 *
 * @since 2.4.4
 * @param $payment_id
 */
function edd_display_subscription_txn_ids( $payment_id ) {

	$is_sub = edd_get_payment_meta( $payment_id, '_edd_subscription_payment' );

	if ( $is_sub ) :
		$subs_db = new EDD_Subscriptions_DB;
		$subs    = $subs_db->get_subscriptions( array( 'parent_payment_id' => $payment_id ) );

		if( ! $subs ) {
			return;
		}
?>
		<div class="edd-subscription-tx-id edd-admin-box-inside">
			<?php foreach( $subs as $sub ) : ?>
				<?php if( ! $sub->get_transaction_id() ) { continue; } ?>
				<p>
					<span class="label"><?php _e( 'Subscription TXN ID:', 'edd-recurring' ); ?></span>&nbsp;
					<span><?php echo apply_filters( 'edd_payment_details_transaction_id-' . $sub->gateway, $sub->get_transaction_id(), $payment_id ); ?></span>
				</p>
			<?php endforeach; ?>
		</div>
<?php
	endif;
}
add_action( 'edd_view_order_details_payment_meta_after', 'edd_display_subscription_txn_ids', 10, 1 );