<?php
/**
 * Add an errors div
 *
 * @since       1.0
 * @return      void
 */
function edds_add_stripe_errors() {
	echo '<div id="edd-stripe-payment-errors"></div>';
}
add_action( 'edd_after_cc_fields', 'edds_add_stripe_errors', 999 );

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to prevent them from getting posted to the server
 *
 * @since       1.7.5
 * @return      void
 */
function edds_credit_card_form( $echo = true ) {

	global $edd_options;

	if( edd_get_option( 'stripe_checkout', false ) ) {
		return;
	}

	ob_start(); ?>

	<?php if ( ! wp_script_is ( 'edd-stripe-js' ) ) : ?>
		<?php edd_stripe_js( true ); ?>
	<?php endif; ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<legend><?php _e( 'Credit Card Info', 'edds' ); ?></legend>
		<?php if( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock">
					<svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
						<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"/>
					</svg>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'edds' ); ?></span>
			</div>
		<?php endif; ?>

		<?php
		$user  = get_userdata( get_current_user_id() );
		$email = $user ? $user->user_email : '';
		$existing_cards = edd_stripe_get_existing_cards( $email );
		?>
		<?php if ( ! empty( $existing_cards ) ) { edd_stripe_existing_card_field_radio( get_current_user_id() ); } ?>

		<div class="edd-stripe-new-card" <?php if ( ! empty( $existing_cards ) ) { echo 'style="display: none;"'; } ?>>
			<?php do_action( 'edd_stripe_new_card_form' ); ?>
			<?php do_action( 'edd_after_cc_expiration' ); ?>
		</div>

	</fieldset>
	<?php

	do_action( 'edd_after_cc_fields' );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}
add_action( 'edd_stripe_cc_form', 'edds_credit_card_form' );

/**
 * Display the markup for the Stripe new card form
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_new_card_form() {
	?>
	<p id="edd-card-number-wrap">
		<label for="card_number" class="edd-label">
			<?php _e( 'Card Number', 'edds' ); ?>
			<span class="edd-required-indicator">*</span>
			<span class="card-type"></span>
		</label>
		<span class="edd-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'edds' ); ?></span>
		<input type="tel" pattern="^[0-9!@#$%^&* ]*$" id="card_number" class="card-number edd-input required" placeholder="<?php _e( 'Card number', 'edds' ); ?>" autocomplete="cc-number" />
	</p>
	<p id="edd-card-cvc-wrap">
		<label for="card_cvc" class="edd-label">
			<?php _e( 'CVC', 'edds' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>
		<span class="edd-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'edds' ); ?></span>
		<input type="tel" pattern="[0-9]{3,4}" size="4" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e( 'Security code', 'edds' ); ?>" autocomplete="cc-csc" />
	</p>
	<p id="edd-card-name-wrap">
		<label for="card_name" class="edd-label">
			<?php _e( 'Name on the Card', 'edds' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>
		<span class="edd-description"><?php _e( 'The name printed on the front of your credit card.', 'edds' ); ?></span>
		<input type="text" id="card_name" class="card-name edd-input required" placeholder="<?php _e( 'Card name', 'edds' ); ?>" autocomplete="cc-name" />
	</p>
	<?php do_action( 'edd_before_cc_expiration' ); ?>
	<p class="card-expiration">
		<label for="card_exp_month" class="edd-label">
			<?php _e( 'Expiration (MM/YY)', 'edds' ); ?>
			<span class="edd-required-indicator">*</span>
		</label>
		<span class="edd-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'edds' ); ?></span>
		<select id="card_exp_month" class="card-expiry-month edd-select edd-select-small required" autocomplete="cc-exp-month">
			<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
		</select>
		<span class="exp-divider"> / </span>
		<select id="card_exp_year" class="card-expiry-year edd-select edd-select-small required" autocomplete="cc-exp-year">
			<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
		</select>
	</p>
	<?php
}
add_action( 'edd_stripe_new_card_form', 'edd_stripe_new_card_form' );

/**
 * Show the checkbox for updating the billing information on an existing Stripe card
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_update_billing_address_field() {
	$payment_mode   = strtolower( edd_get_chosen_gateway() );
	if ( 'stripe' !== $payment_mode ) {
		return;
	}

	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
	if ( empty( $existing_cards ) ) {
		return;
	}

	if ( ! did_action( 'edd_stripe_cc_form' ) ) {
		return;
	}
	?>
	<p class="edd-stripe-update-billing-address-wrapper">
		<input type="checkbox" name="edd_stripe_update_billing_address" id="edd-stripe-update-billing-address" value="1" />
		<label for="edd-stripe-update-billing-address"><?php _e( 'Update billing address', 'edds' ); ?></label>
	</p>
	<?php
}
add_action( 'edd_cc_billing_top', 'edd_stripe_update_billing_address_field', 10 );

/**
 * Display a radio list of existing cards on file for a user ID
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return void
 */
function edd_stripe_existing_card_field_radio( $user_id = 0 ) {
	edd_stripe_css( true );
	$existing_cards = edd_stripe_get_existing_cards( $user_id );
	if ( ! empty( $existing_cards ) ) : ?>
	<script>
		jQuery(document).ready(function($) { $('.edd-stripe-existing-card:first').trigger('click', $(this)); });
	</script>
	<div class="edd-stripe-card-selector edd-card-selector-radio">
		<?php foreach ( $existing_cards as $card ) : ?>
			<?php $source = $card['source']; ?>
			<div class="edd-stripe-card-radio-item existing-card-wrapper <?php if ( $card['default'] ) { echo ' selected'; } ?>">
				<input type="hidden" id="<?php echo $source->id; ?>-billing-details"
					   data-address_city="<?php echo $source->address_city; ?>"
					   data-address_country="<?php echo $source->address_country; ?>"
					   data-address_line1="<?php echo $source->address_line1; ?>"
					   data-address_line2="<?php echo $source->address_line2; ?>"
					   data-address_state="<?php echo $source->address_state; ?>"
					   data-address_zip="<?php echo $source->address_zip; ?>"
				/>
				<label for="<?php echo $source->id; ?>">
					<input <?php checked( true, $card['default'], true ); ?> type="radio" id="<?php echo $source->id; ?>" name="edd_stripe_existing_card" value="<?php echo $source->id; ?>" class="edd-stripe-existing-card">
					<span class="card-label">
						<span class="card-data">
							<span class="card-name-number">
								<span class="card-brand"><?php echo $source->brand; ?></span>
								<span class="card-ending-label"><?php _e( 'ending in', 'edds' ); ?></span>
								<span class="card-last-4"><?php echo $source->last4; ?></span>
							</span>
							<span class="card-expires-on">
								<span class="default-card-sep"><?php echo '&mdash; '; ?></span>
								<span class="card-expiration-label"><?php _e( 'expires', 'edds' ); ?></span>
								<span class="card-expiration">
									<?php echo $source->exp_month . '/' . $source->exp_year; ?>
								</span>
							</span>
						</span>
						<?php
							$current  = strtotime( date( 'm/Y' ) );
							$exp_date = strtotime( $source->exp_month . '/' . $source->exp_year );
							if ( $exp_date < $current ) :
							?>
							<span class="card-expired">
									<?php _e( 'Expired', 'edds' ); ?>
								</span>
							<?php
							endif;
						?>
					</span>
					<?php if ( $card['default'] ) { ?>
						<span class="card-status">
							<span class="default-card-sep"><?php echo '&mdash; '; ?></span>
							<span class="card-is-default"><?php _e( 'Default', 'edds'); ?></span>
						</span>
					<?php } ?>
				</label>
			</div>
		<?php endforeach; ?>
		<div class="edd-stripe-card-radio-item new-card-wrapper">
			<input type="radio" id="edd-stripe-add-new" class="edd-stripe-existing-card" name="edd_stripe_existing_card" value="new" />
			<label for="edd-stripe-add-new"><span class="add-new-card"><?php _e( 'Add New Card', 'edds' ); ?></span></label>
		</div>
	</div>
	<?php endif;
}

/**
 * Output the management interface for a user's Stripe card
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_manage_cards() {
	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		return;
	}

	$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );

	if ( empty( $existing_cards ) ) {
		return;
	}

	edd_stripe_css( true );
	edd_stripe_js( true );
	$display = edd_get_option( 'stripe_billing_fields', 'full' );
	?>
	<form id="edd-stripe-manage-cards">
		<fieldset>
			<legend><?php _e( 'Manage Payment Methods', 'edds' ); ?></legend>
			<input type="hidden" id="stripe-update-card-user_id" name="stripe-update-user-id" value="<?php echo get_current_user_id(); ?>" />
			<?php if ( ! empty( $existing_cards ) ) : ?>
				<?php foreach( $existing_cards as $card ) : ?>
				<?php $source = $card['source']; ?>
				<div class="edd-stripe-card-item">

					<span class="card-details">
						<span class="card-brand"><?php echo $source->brand; ?></span>
						<span class="card-ending-label"><?php _e( 'Ending in', 'edds' ); ?></span>
						<span class="card-last-4"><?php echo $source->last4; ?></span>
						<?php if ( $card['default'] ) { ?>
							<span class="default-card-sep"><?php echo '&mdash; '; ?></span>
							<span class="card-is-default"><?php _e( 'Default', 'edds'); ?></span>
						<?php } ?>
					</span>

					<span class="card-meta">
						<span class="card-expiration"><span class="card-expiration-label"><?php _e( 'Expires', 'edds' ); ?>: </span><span class="card-expiration-date"><?php echo $source->exp_month; ?>/<?php echo $source->exp_year; ?></span></span>
						<span class="card-address">
							<?php $address_fields = array_values( array( $source->address_line1, $source->address_zip, $source->address_country ) ); ?>
							<?php echo implode( ' ', $address_fields ); ?>
						</span>
					</span>

					<span class="card-actions">
						<span class="card-update"><a href="#" class="edd-stripe-update-card"><?php _e( 'Update', 'edds' ); ?></a></span>
						<?php if ( ! $card['default'] ) : ?>
						 | <span class="card-set-as-default"><a href="#" class="edd-stripe-default-card"><?php _e( 'Set as Default', 'edds' ); ?></a></span>
						 | <span class="card-delete"><a href="#" class="edd-stripe-delete-card delete"><?php _e( 'Delete', 'edds' ); ?></a></span>
						<?php endif; ?>
					</span>

					<div class="card-update-form">
						<label><?php _e( 'Update Billing Details', 'edds' ); ?></label>
						<p class="card-address-fields">
							<input type="text" placeholder="<?php _e( 'Address Line 1', 'edds' ); ?>" class="card-update-field address_line1" data-key="address_line1" value="<?php echo $source->address_line1; ?>" />
							<input type="text" placeholder="<?php _e( 'Address Line 2', 'edds' ); ?>" class="card-update-field address_line2" data-key="address_line2" value="<?php echo $source->address_line2; ?>" />
							<input type="text" placeholder="<?php _e( 'City', 'edds' ); ?>" class="card-update-field address_city" data-key="address_city" value="<?php echo $source->address_city; ?>" />
							<input type="text" placeholder="<?php _e( 'Zip Code', 'edds' ); ?>" class="card-update-field address_zip" data-key="address_zip" value="<?php echo $source->address_zip; ?>" />
							<?php
							$countries = array_filter( edd_get_country_list() );
							echo EDD()->html->select( array(
								'options'          => $countries,
								'selected'         => $source->address_country,
								'class'            => 'card-update-field address_country',
								'data'             => array( 'key' => 'address_country' ),
								'show_option_all'  => false,
								'show_option_none' => false,
							));

							$selected_state = ! empty( $source->address_state ) ? $source->address_state : edd_get_shop_state();
							$states         = edd_get_shop_states( $source->address_country );
							echo EDD()->html->select( array(
								'options'          => $states,
								'selected'         => $selected_state,
								'class'            => 'card-update-field address_state card_state',
								'data'             => array( 'key' => 'address_state' ),
								'show_option_all'  => false,
								'show_option_none' => false,
							));
							?>
						</p>
						<p class="card-expiration-fields">
							<label for="card_exp_month" class="edd-label">
								<?php _e( 'Expiration (MM/YY)', 'edds' ); ?>
							</label>
							<select id="card_exp_month" data-key="exp_month" class="card-expiry-month edd-select edd-select-small card-update-field exp_month">
								<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option ' . selected( $source->exp_month, $i, false ) . ' value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
							</select>
							<span class="exp-divider"> / </span>
							<select id="card_exp_year" data-key="exp_year" class="card-expiry-year edd-select edd-select-small card-update-field exp_year">
								<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option ' . selected( $source->exp_year, $i, false ) . ' value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
							</select>
						</p>
						<p>
							<button class="edd-stripe-submit-update"><span class="button-text"><?php _e( 'Update Card', 'edds' ); ?></span><span style="display: none;" class="edd-loading-ajax edd-loading"></span></button> <a href="#" class="edd-stripe-cancel-update"><?php _e( 'Cancel', 'edds' ); ?></a>
							<input type="hidden" name="card_id" data-key="id" value="<?php echo $source->id; ?>" />
							<?php wp_nonce_field( $source->id . '_update', 'card_update_nonce', true ); ?>
						</p>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<div class="edd-stripe-add-new-card" style="display: none;">
				<label><?php _e( 'Add New Card', 'edds' ); ?></label>
				<fieldset id="edd_cc_card_info" class="cc-card-info">
					<legend><?php _e( 'Credit Card Details', 'easy-digital-downloads' ); ?></legend>
					<?php
					edd_stripe_new_card_form();
					?>
				</fieldset>
				<?php
				switch( $display ) {
				case 'full' :
					edd_default_cc_address_fields();
					break;

				case 'zip_country' :
					edd_stripe_zip_and_country();
					add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

					break;
				}
				?>
			</div>
			<div class="edd-stripe-add-card-errors"></div>
			<div class="edd-stripe-add-card-actions">
				<button class="edd-button edd-stripe-add-new">
					<span class="button-text"><?php _e( 'Add new card', 'edds' ); ?></span>
					<span style="display: none;" class="edd-loading-ajax edd-loading"></span>
				</button>
				<a href="#" id="edd-stripe-add-new-cancel" style="display: none;"><?php _e( 'Cancel', 'edds' ); ?></a>
				<?php wp_nonce_field( 'edd-stripe-add-card', 'edd-stripe-add-card-nonce', false, true ); ?>
			</div>
		</fieldset>
	</form>
	<?php
}
add_action( 'edd_profile_editor_after', 'edd_stripe_manage_cards' );

/**
 * Zip / Postal Code field for when full billing address is disabled
 *
 * @since       2.5
 * @return      void
 */
function edd_stripe_zip_and_country() {

	$logged_in = is_user_logged_in();
	$customer  = EDD()->session->get( 'customer' );
	$customer  = wp_parse_args( $customer, array( 'address' => array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => ''
	) ) );

	$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

	if( $logged_in ) {
		$existing_cards = edd_stripe_get_existing_cards( get_current_user_id() );
		if ( empty( $existing_cards ) ) {
			$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );

			foreach( $customer['address'] as $key => $field ) {

				if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
					$customer['address'][ $key ] = $user_address[ $key ];
				} else {
					$customer['address'][ $key ] = '';
				}

			}
		} else {
			foreach ( $existing_cards as $card ) {
				if ( false === $card['default'] ) {
					continue;
				}

				$source = $card['source'];
				$customer['address'] = array(
					'line1'   => $source->address_line1,
					'line2'   => $source->address_line2,
					'city'    => $source->address_city,
					'zip'     => $source->address_zip,
					'state'   => $source->address_state,
					'country' => $source->address_country,
				);
			}
		}

	}
?>
	<fieldset id="edd_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'edds' ); ?></legend>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Billing Country', 'edds' ); ?>
				<?php if( edd_field_is_required( 'billing_country' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'edds' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country edd-select<?php if( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?> autocomplete="billing country">
				<?php

				$selected_country = edd_get_shop_country();

				if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Billing Zip / Postal Code', 'edds' ); ?>
				<?php if( edd_field_is_required( 'card_zip' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'edds' ); ?></span>
			<input type="text" size="4" name="card_zip" id="card_zip" class="card-zip edd-input<?php if( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'edds' ); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?> autocomplete="billing postal-code" />
		</p>
	</fieldset>
<?php
}

/**
 * Determine how the billing address fields should be displayed
 *
 * @access      public
 * @since       2.5
 * @return      void
 */
function edd_stripe_setup_billing_address_fields() {

	if( ! function_exists( 'edd_use_taxes' ) ) {
		return;
	}

	if( edd_use_taxes() || edd_get_option( 'stripe_checkout' ) || 'stripe' !== edd_get_chosen_gateway() || ! edd_get_cart_total() > 0 ) {
		return;
	}

	$display = edd_get_option( 'stripe_billing_fields', 'full' );

	switch( $display ) {

		case 'full' :

			// Make address fields required
			add_filter( 'edd_require_billing_address', '__return_true' );

			break;

		case 'zip_country' :

			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );
			add_action( 'edd_after_cc_fields', 'edd_stripe_zip_and_country', 9 );

			// Make Zip required
			add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

			break;

		case 'none' :

			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );

			break;

	}

}
add_action( 'init', 'edd_stripe_setup_billing_address_fields', 9 );

/**
 * Force zip code and country to be required when billing address display is zip only
 *
 * @access      public
 * @since       2.5
 * @return      array $fields The required fields
 */
function edd_stripe_require_zip_and_country( $fields ) {

	$fields['card_zip'] = array(
		'error_id' => 'invalid_zip_code',
		'error_message' => __( 'Please enter your zip / postal code', 'edds' )
	);

	$fields['billing_country'] = array(
		'error_id' => 'invalid_country',
		'error_message' => __( 'Please select your billing country', 'edds' )
	);

	return $fields;
}

/**
 * Outputs javascript for the Stripe Checkout modal
 *
 * @since  2.0
 * @return void
 */
function edd_stripe_purchase_link_output( $download_id = 0, $args = array() ) {
	global $printed_stripe_purchase_link;

	// Stop our output from being triggered if someone is looking at the content for meta tags, like Jetpack
	if ( doing_action( 'wp_head' ) ) {
		return;
	}

	if ( ! empty( $printed_stripe_purchase_link[ $download_id ] ) ) {
		return;
	}

	if( ! isset( $args['stripe-checkout'] ) ) {
		return;
	}

	if( ! edd_is_gateway_active( 'stripe' ) ) {
		return;
	}

	edd_stripe_js( true );

	if ( edd_is_test_mode() ) {
		$publishable_key = trim( edd_get_option( 'test_publishable_key' ) );
	} else {
		$publishable_key = trim( edd_get_option( 'live_publishable_key' ) );
	}

	$download = get_post( $download_id );

	$email = '';
	if( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$email        = $current_user->user_email;
	}
	?>
	<script>
		jQuery(document).ready(function($) {

			var edd_global_vars;
			var edd_scripts;
			var form;

			$('#edd_purchase_<?php echo $download_id; ?> .edd-add-to-cart,.edd_purchase_<?php echo $download_id; ?> .edd-add-to-cart').click(function(e) {

				form = $(this).parents('.edd_download_purchase_form');

				e.preventDefault();

				var label = form.find('.edd-add-to-cart-label').text();

				if( form.find( '.edd_price_options' ).length || form.find( '.edd_price_option_<?php echo $download_id; ?>' ).length ) {

					var custom_price = false;
					var price_id;
					var prices = [];
					var amount = 0;

					<?php foreach( edd_get_variable_prices( $download_id ) as $price_id => $price ) : ?>
						prices[<?php echo $price_id; ?>] = <?php echo $price['amount']; ?>;
					<?php endforeach; ?>

					if( form.find( '.edd_price_option_<?php echo $download_id; ?>' ).length > 1 ) {

						if( form.find('.edd_price_options input:checked').hasClass( 'edd_cp_radio' ) ) {

							custom_price = true;
							amount = form.find( '.edd_cp_price' ).val();

						} else {
							price_id = form.find('.edd_price_options input:checked').val();
						}

					} else {

						price_id = form.find('.edd_price_option_<?php echo $download_id; ?>').val();

					}

					if( ! custom_price ) {

						amount = prices[ price_id ];

					}

				} else if( form.find( '.edd_cp_price' ).length && form.find( '.edd_cp_price' ).val() ) {
					amount = form.find( '.edd_cp_price' ).val();

				} else {
					amount = <?php echo edd_get_download_price( $download_id ); ?>;
				}

				if ( 'true' != edd_stripe_vars.is_zero_decimal ) {
					amount *= 100;
					amount = Math.round( amount );
				}

				StripeCheckout.configure({
					key: '<?php echo $publishable_key; ?>',
					locale: '<?php echo edds_get_stripe_checkout_locale(); ?>',
					//image: '/square-image.png',
					token: function(token) {
						// insert the token into the form so it gets submitted to the server
						form.append("<input type='hidden' name='edd_stripe_token' value='" + token.id + "' />");
						form.append("<input type='hidden' name='edd_email' value='" + token.email + "' />");
						// submit
						form.get(0).submit();
					},
					opened: function() {

					},
					closed: function() {
						form.find('.edd-add-to-cart').removeAttr( 'data-edd-loading' );
						form.find('.edd-add-to-cart-label').text( label ).show();
					}
				}).open({
					name: '<?php echo esc_js( get_bloginfo( "name" ) ); ?>',
					image: '<?php echo esc_url( edd_get_option( "stripe_checkout_image" ) ); ?>',
					description: '<?php echo esc_js( $download->post_title ); ?>',
					amount: Math.round( amount ),
					zipCode: <?php echo edd_get_option( 'stripe_checkout_zip_code' ) ? 'true' : 'false'; ?>,
					allowRememberMe: <?php echo edd_get_option( 'stripe_checkout_remember' ) ? 'true' : 'false'; ?>,
					billingAddress: <?php echo edd_get_option( 'stripe_checkout_billing' ) ? 'true' : 'false'; ?>,
					email: '<?php echo esc_js( $email ); ?>',
					currency: '<?php echo edd_get_currency(); ?>'
				});

				return false;

			});

		});
	</script>
<?php
	$printed_stripe_purchase_link[ $download_id ] = true;
}
add_action( 'edd_purchase_link_end', 'edd_stripe_purchase_link_output', 99999, 2 );