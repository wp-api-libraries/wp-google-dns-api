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
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Start building query.
			$this->args['method'] = $method;
			$this->route = $route;

			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}


		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->clear();
			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-google-dns-api' ), $code ), $body );
			}

			return $body;
		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int     $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}

		/**
		 * Resolve DNS.
		 *
		 * @access public
		 * @param string  $name               The only required parameter. Its length must be between 1 and 253 (ignoring an
		 *                                    optional trailing dot if present). All labels (parts of the name separated by
		 *                                    dots) must be 1 to 63 bytes long. The API does not support names with escaped
		 *                                    or non-ASCII characters, but they are not explicitly rejected. Internationalized
		 *                                    domain names must use punycode format (e.g. "xn--qxam" rather than "ελ").
		 * @param string  $type               RR type can be represented as a number in [1, 65535] or a canonical string
		 *                                    (case-insensitive, such as A or aaaa). You can use 255 for 'ANY' queries but
		 *                                    be aware that this is not a replacement for sending queries for both A and
		 *                                    AAAA or MX records. Authoritative name servers need not return all records for
		 *                                    such queries; some do not respond, and others (such as cloudflare.com) return
		 *                                    only HINFO.
		 * @param boolean $cd                 The CD (checking disabled) bit. Use cd, cd=1, or cd=true to disable DNSSEC
		 *                                    validation; use cd=0, cd=false, or no cd parameter to enable DNSSEC validation.
		 * @param string  $edns_client_subnet The edns0-client-subnet option. Format is an IP address with a subnet mask.
		 *                                    Examples: 1.2.3.4/24, 2001:700:300::/48. If you are using DNS-over-HTTPS
		 *                                    because of privacy concerns, and do not want any part of your IP address to be
		 *                                    sent to authoritative name servers for geographic location accuracy, use
		 *                                    edns_client_subnet=0.0.0.0/0. Google Public DNS normally sends approximate
		 *                                    network information (usually zeroing out the last part of your IPv4 address).

		 * @param string  $random_padding     The value of this parameter is ignored. Example: XmkMw~o_mgP2pf.gpw-Oi5dK. API
		 *                                    clients concerned about possible side-channel privacy attacks using the packet
		 *                                    sizes of HTTPS GET requests can use this to make all requests exactly the same
		 *                                    size by padding requests with random data. To prevent misinterpretation of the
		 *                                    URL, restrict the padding characters to the unreserved URL characters: upper-
		 *                                    and `lower-case letters, digits, hyphen, period, underscore and tilde.
		 * @return array                      DNS Lookup results.
		 */
		function resolve_dns( string $name, string $type = null, bool $cd = null, string $edns_client_subnet = null, string $random_padding = null ) {

			if ( empty( $name ) ) {
				return new WP_Error( 'response-error', __( "Please provide the Name.", "wp-google-dns-api" ) );
			}

			$args['name'] = $name;
			if( ! is_null( $type ) ){
				$args['type'] = $type;
			}
			if( ! is_null( $cd ) ){
				$args['cd'] = $cd;
			}
			if( ! is_null( $edns_client_subnet ) ){
				$args['edns_client_subnet'] = $edns_client_subnet;
			}
			if( ! is_null( $random_padding ) ){
				$args['random_padding'] = $random_padding;
			}

			return $this->build_request( '', $args )->fetch();
		}

	}
}
