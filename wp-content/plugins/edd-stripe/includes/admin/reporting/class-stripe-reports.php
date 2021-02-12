<?php
/**
 * Reporting: Stripe
 *
 * @package EDD_Stripe
 * @since   2.6
 */

/**
 * Class EDD_Stripe_Reports
 *
 * Do nothing in 2.8.0
 * The reports have not collected data since 2.7.0 and provide no tangible value.
 *
 * @since 2.6
 * @deprecated 2.8.0
 */
class EDD_Stripe_Reports {
	public function __construct() {
		_doing_it_wrong(
			__CLASS__,
			__( 'Stripe-specific reports have been removed.', 'edds' ),
			'2.8.0'
		);
	}
}