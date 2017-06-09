<?php
/**
 * Process the card update actions from the manage card form.
 *
 * @since 2.6
 * @param $data
 * @return void
 */
function edd_stripe_process_card_update( $data ) {
	$response = array();

	$card_id  = isset( $data['card_id'] ) ? sanitize_text_field( $data['card_id'] ) : '';
	if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], $card_id . '_update' ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Error updating card.', 'edds' );
	} elseif ( empty ( $card_id ) || empty( $data['user_id'] ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Missing card or user ID.', 'edds' );
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		$response['success'] = false;
		$response['message'] = __( 'This feature is not available at this time.', 'edds' );
	}

	if ( ! isset( $response['success'] ) || false !== $response['success'] ) {
		$stripe_customer_id = edds_get_stripe_customer_id( $data['user_id'] );
		if ( ! empty( $stripe_customer_id ) ) {
			$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) ) ;

			\Stripe\Stripe::setApiKey( $secret_key );
			$stripe_customer = \Stripe\Customer::retrieve( $stripe_customer_id );

			$card = $stripe_customer->sources->retrieve( $card_id );
			foreach( $data['card_data'] as $key => $value ) {
				if ( ! empty( $value ) ) {
					$card->$key = $value;
				} else {
					$card->$key = null;
				}
			}

			$card->save();
			$response['success'] = true;
			$response['message'] = __( 'Card successfully updated', 'edds' );
		}
	}

	echo json_encode( $response );
	die();
}
add_action( 'edd_update_stripe_card', 'edd_stripe_process_card_update', 10, 1 );

/**
 * Process the set default card action from the manage card form.
 *
 * @since 2.6
 * @param $data
 * @return void
 */
function edd_stripe_process_card_default( $data ) {
	$response = array();

	$card_id  = isset( $data['card_id'] ) ? sanitize_text_field( $data['card_id'] ) : '';
	if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], $card_id . '_update' ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Error updating card.', 'edds' );
	} elseif ( empty ( $card_id ) || empty( $data['user_id'] ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Missing card or user ID.', 'edds' );
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		$response['success'] = false;
		$response['message'] = __( 'This feature is not available at this time.', 'edds' );
	}

	if ( ! isset( $response['success'] ) || false !== $response['success'] ) {
		$stripe_customer_id = edds_get_stripe_customer_id( $data['user_id'] );
		if ( ! empty( $stripe_customer_id ) ) {
			$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) ) ;

			\Stripe\Stripe::setApiKey( $secret_key );
			$stripe_customer = \Stripe\Customer::retrieve( $stripe_customer_id );
			$stripe_customer->default_source = $card_id;
			$stripe_customer->save();

			$response['success'] = true;
			$response['message'] = __( 'Default card updated successfully.', 'edds' );
		}
	}

	echo json_encode( $response );
	die();
}
add_action( 'edd_set_default_card', 'edd_stripe_process_card_default', 10, 1 );

/**
 * Process the delete card action from the manage card form.
 *
 * @since 2.6
 * @param $data
 * @return void
 */
function edd_stripe_process_card_delete( $data ) {
	$response = array();

	$card_id  = isset( $data['card_id'] ) ? sanitize_text_field( $data['card_id'] ) : '';
	if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], $card_id . '_update' ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Error removing card.', 'edds' );
	} elseif ( empty ( $card_id ) || empty( $data['user_id'] ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Missing card or user ID.', 'edds' );
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		$response['success'] = false;
		$response['message'] = __( 'This feature is not available at this time.', 'edds' );
	}

	if ( ! isset( $response['success'] ) || false !== $response['success'] ) {
		$stripe_customer_id = edds_get_stripe_customer_id( $data['user_id'] );
		if ( ! empty( $stripe_customer_id ) ) {
			$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) ) ;

			\Stripe\Stripe::setApiKey( $secret_key );
			$stripe_customer = \Stripe\Customer::retrieve( $stripe_customer_id );
			$deleted = $stripe_customer->sources->retrieve( $card_id )->delete();
			if ( $deleted->deleted ) {
				$response['success'] = true;
				$response['message'] = __( 'Card successfully removed.', 'edds' );
			} else {
				$response['success'] = false;
				$response['message'] = __( 'Error removing card.', 'edds' );
			}
		}
	}

	echo json_encode( $response );
	die();
}
add_action( 'edd_delete_stripe_card', 'edd_stripe_process_card_delete', 10, 1 );

/**
 * Process the add card action from the manage card form.
 *
 * @since 2.6
 * @param $data
 * @return void
 */
function edd_stripe_process_card_add( $data ) {
	$response = array();

	$token  = isset( $data['token'] ) ? sanitize_text_field( $data['token'] ) : '';
	if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'edd-stripe-add-card' ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Error adding card.', 'edds' );
	} elseif ( empty ( $token ) || empty( $data['user_id'] ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Missing token or user ID.', 'edds' );
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		$response['success'] = false;
		$response['message'] = __( 'This feature is not available at this time.', 'edds' );
	}

	if ( ! isset( $response['success'] ) || false !== $response['success'] ) {
		$stripe_customer_id = edds_get_stripe_customer_id( $data['user_id'] );
		if ( ! empty( $stripe_customer_id ) ) {
			$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) ) ;

			\Stripe\Stripe::setApiKey( $secret_key );
			$stripe_customer = \Stripe\Customer::retrieve( $stripe_customer_id );

			$added = $stripe_customer->sources->create( array( 'source' => $token ) );
			if ( $added->id ) {
				$response['success'] = true;
				$response['message'] = __( 'Card successfully added.', 'edds' );
			} else {
				$response['success'] = false;
				$response['message'] = __( 'Error adding card.', 'edds' );
			}
		}
	}

	echo json_encode( $response );
	die();
}
add_action( 'edd_add_stripe_card', 'edd_stripe_process_card_add', 10, 1 );