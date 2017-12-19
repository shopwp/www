<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Cache {

	const CACHE_PREFIX = 'filtering-';

	/**
	 * @var string Cache id
	 */
	private $cache_id;

	/**
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->set_cache_id( md5( self::CACHE_PREFIX . $name ) ); // 32 characters
	}

	/**
	 * Set Cache id. Max length for site_transient name is 40 characters,
	 *
	 * @param string $name
	 * @source https://core.trac.wordpress.org/ticket/15058
	 */
	private function set_cache_id( $name ) {
		$this->cache_id = substr( $name, 0, 40 );
	}

	/**
	 * @see get_transient
	 * @return mixed
	 */
	public function get() {
		return get_site_transient( $this->cache_id );
	}

	/**
	 * @see set_transient
	 *
	 * @param mixed $value
	 * @param int $time Default is "no expiration"
	 */
	public function set( $value, $expiration = 0 ) {
		set_site_transient( $this->cache_id, $value, $expiration );
	}

	/**
	 * @see delete_transient
	 */
	public function delete() {
		delete_site_transient( $this->cache_id );
	}

	/**
	 * Time left on cache in seconds, unless it's being done with an external cache
	 *
	 * @return int|string Time left in seconds
	 */
	public function time_left() {

		// external cache does not have a timer available
		if ( wp_using_ext_object_cache() ) {
			return 'external';
		}

		return max( get_option( '_site_transient_timeout_' . $this->cache_id ) - time(), 0 );
	}

}
