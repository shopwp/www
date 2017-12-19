<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Columns Pro website connection API
 *
 * @since 3.0
 */
class ACP_License_API {

	/**
	 * API url
	 *
	 * @since 3.1.2
	 * @var $url string
	 */
	public $url;

	/**
	 * Request args
	 *
	 * @since 3.1.2
	 * @var $request_arg array
	 */
	public $request_args;

	/**
	 * Constructor
	 *
	 * @since 3.1.2
	 */
	public function __construct() {
		$this->request_args = array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => array(
				'wc-api' => 'software-licence-api',
			),
		);
	}

	/**
	 * Set url
	 *
	 * @since 3.1.2
	 *
	 * @param $api_url
	 *
	 * @return ACP_License_API
	 */
	public function set_url( $api_url ) {
		$this->url = $api_url;

		return $this;
	}

	/**
	 * Get url
	 *
	 * @since 3.1.2
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Set request arg
	 *
	 * @since 3.1.2
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return ACP_License_API
	 */
	public function set_request_arg( $key, $value ) {
		$this->request_args[ $key ] = $value;

		return $this;
	}

	/**
	 * Get request args
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_request_args() {
		return $this->request_args;
	}

	/**
	 * Activate a license by its license key
	 *
	 * @since 3.0
	 *
	 * @param string $licence_key Licence Key
	 *
	 * @return mixed API Response
	 */
	public function activate_licence( $licence_key ) {

		$response = $this->request( array(
			'request'     => 'activation',
			'licence_key' => $licence_key,
			'site_url'    => site_url(),
		) );

		return $response;
	}

	/**
	 * Activate a license by its license key
	 *
	 * @since 3.0
	 *
	 * @param $licence_key
	 *
	 * @return mixed API Response
	 */
	public function deactivate_licence( $licence_key ) {

		$response = $this->request( array(
			'request'     => 'deactivation',
			'licence_key' => $licence_key,
			'site_url'    => site_url() // identifying
		) );

		return $response;
	}

	/**
	 * Plugin HTML changelog
	 *
	 * @since 3.0
	 *
	 * @param $plugin_basename
	 *
	 * @return mixed API Response
	 */
	public function get_plugin_changelog( $plugin_basename ) {

		$response = $this->request( array(
			'request'     => 'pluginchangelog',
			'plugin_name' => $plugin_basename,
		), 'html' );

		return $response;
	}

	/**
	 * Get remote plugin update data in the WP format: url, slug, package, new_version, id
	 *
	 * @since 1.1
	 *
	 * @param string $licence_key Licence Key
	 * @param string $plugin_basename Plugin basename
	 *
	 * @return mixed API Response
	 */
	public function get_plugin_install_data( $licence_key, $plugin_basename ) {

		$response = $this->request( array(
			'request'     => 'plugininstall',
			'licence_key' => $licence_key,
			'plugin_name' => $plugin_basename,
		) );

		return $response;
	}

	/**
	 * Get remote plugin update data in the WP format: url, slug, package, new_version, id (old:name, slug, download_link, version)
	 *
	 * @since 1.1
	 *
	 * @param string $licence_key Licence Key
	 * @param string $plugin_basename Plugin basename
	 * @param string $current_version Plugin's current version
	 *
	 * @return mixed API Response
	 */
	public function get_plugin_update_data( $licence_key, $plugin_basename, $current_version ) {

		$response = $this->request( array(
			'request'     => 'pluginupdate',
			'licence_key' => $licence_key,
			'plugin_name' => $plugin_basename,
			'version'     => $current_version,
		) );

		return $response;
	}

	/**
	 * Get remote plugin update data in the WP format: ...
	 *
	 * @since 1.1
	 *
	 * @param string $plugin_basename
	 *
	 * @return mixed API Response
	 */
	public function get_plugin_details( $plugin_basename ) {

		$response = $this->request( array(
			'request'     => 'plugindetails',
			'plugin_name' => $plugin_basename,
		) );

		return $response;
	}

	/**
	 * Get license details
	 *
	 * @since 3.4.3
	 *
	 * @param string $license_key
	 *
	 * @return mixed API Response
	 */
	public function get_license_details( $license_key ) {

		$response = $this->request( array(
			'request'     => 'licensedetails',
			'license_key' => $license_key,
		) );

		return $response;
	}

	/**
	 * Test request
	 *
	 * @since 3.1.2
	 *
	 * @param string $plugin_basename
	 *
	 * @return mixed API response
	 */
	public function test_request( $plugin_basename ) {
		$response = $this->get_plugin_details( $plugin_basename );

		return is_wp_error( $response ) && 'http_request_failed' == $response->get_error_code() ? false : true;
	}

	/**
	 * API Request
	 *
	 * @since 1.1
	 *
	 * @param array $body
	 * @param string $format
	 *
	 * @return mixed API Response
	 */
	protected function request( $body, $format = 'json' ) {

		$this->request_args['body'] = array_merge( $this->request_args['body'], $body );

		$result = wp_remote_post( $this->url, $this->request_args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$response = wp_remote_retrieve_body( $result );

		if ( 'json' == $format ) {
			$response = json_decode( $response );
		}

		if ( isset( $response->error ) ) {
			return new WP_Error( $response->code, $response->message );
		}
		elseif ( empty( $response ) ) {
			return new WP_Error( 'empty_response', __( 'Empty response from API.', 'codepress-admin-columns' ) );
		}

		return $response;
	}

}
