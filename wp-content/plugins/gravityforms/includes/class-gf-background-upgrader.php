<?php
/**
 * Background Upgrader
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    GF_Background_Upgrader
 * @version  2.3
 * @category Class
 * @author   Rocketgenius
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'libraries/wp-async-request.php' );
require_once( 'libraries/gf-background-process.php' );

/**
 * GF_Background_Upgrader Class.
 */
class GF_Background_Upgrader extends GF_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'gf_upgrader';

	/**
	 * Dispatch upgrader.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 *
	 * @return array|WP_Error
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			GFCommon::log_debug( sprintf( 'Unable to dispatch upgrader: %s', $dispatched->get_error_message() ) );
		}

		return $dispatched;
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Is the queue empty for all blogs?
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_queue_empty() {
		return parent::is_queue_empty();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'GF_UPGRADING' ) ) {
			define( 'GF_UPGRADING', true );
		}

		if ( is_callable( $callback ) ) {
			GFCommon::log_debug( sprintf( 'Running callback: %s', print_r( $callback, 1 ) ) );
			$needs_more_time = call_user_func( $callback );
			if ( $needs_more_time ) {
				GFCommon::log_debug( sprintf( 'Callback needs another run: %s', print_r( $callback, 1 ) ) );
				return $callback;
			} else {
				GFCommon::log_debug( sprintf( 'Finished callback: %s', print_r( $callback, 1 ) ) );
			}
		} else {
			GFCommon::log_debug( sprintf( 'Could not find callback: %s', print_r( $callback, 1 ) ) );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();
	}

}
