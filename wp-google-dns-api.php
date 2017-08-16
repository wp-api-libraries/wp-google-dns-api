<?php
/**
 * WP-GoogleDNS-API
 *
 * @link https://developers.google.com/speed/public-dns/docs/dns-over-https API Docs
 * @package WP-API-Libraries\WP-Google-DNS-API
 */

/*
* Plugin Name: WP Google DNS API
* Plugin URI: https://github.com/wp-api-libraries/wp-google-dns-api
* Description: Perform API requests to Google DNS in WordPress.
* Author: imFORZA
* Version: 1.0.0
* Author URI: https://www.imforza.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-google-dns-api
* GitHub Branch: master
*/

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Check if class exists. */
if ( ! class_exists( 'GoogleDnsAPI' ) ) {

	/**
	 * GoogleDNS API Class.
	 *
	 * @link https://developers.google.com/speed/public-dns/docs/dns-over-https API Docs
	 */
	class GoogleDnsAPI {

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://dns.google.com/resolve';


		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
		}

		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @param mixed $request Request URL.
		 * @return $body Body.
		 */
		private function fetch( $request ) {

			$response = wp_remote_get( $request );
			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'text-domain' ), $code ) );
			}

			$body = wp_remote_retrieve_body( $response );

			return json_decode( $body );

		}

		/**
		 * Resolve DNS.
		 *
		 * @access public
		 * @param mixed $name Name.
		 * @param mixed $type Type.
		 * @param mixed $cd CD.
		 * @param mixed $edns_client_subnet
		 * @param mixed $random_padding Random Padding to the Request.
		 * @return void
		 */
		function resolve_dns( $name, $type = '1', $cd = 'false', $edns_client_subnet = '', $random_padding = '' ) {

			if ( empty( $name ) ) {
				return new WP_Error( 'response-error', __( "Please provide the Name.", "text-domain" ) );
			}

			$request = $this->base_uri . '?name=' . $name;

			return $this->fetch( $request );

		}

	}
}
