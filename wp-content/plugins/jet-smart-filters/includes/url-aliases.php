<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_URL_Aliases' ) ) {
	/**
	 * Define Jet_Smart_Filters_URL_Aliases class
	 */
	class Jet_Smart_Filters_URL_Aliases {

		public $use_url_aliases;
		public $url_aliases;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			$this->use_url_aliases = filter_var( jet_smart_filters()->settings->get( 'use_url_aliases' ), FILTER_VALIDATE_BOOLEAN );
			$this->url_aliases     = jet_smart_filters()->settings->get( 'url_aliases' );

			if ( $this->use_url_aliases && ! jet_smart_filters()->utils->is_rest_request() ) {
				add_filter( 'do_parse_request', function( $do_parse_request ) {
					if ( $do_parse_request ) {
						$this->parse_aliases();
					}

					return $do_parse_request;
				}, 9999 );
			}
		}

		public function apply_aliases_to_url( $url ) {

			if ( ! $this->url_aliases ) {
				return $url;
			}

			foreach ( $this->url_aliases as $alias ) {
				if ( ! $alias['needle'] || ! $alias['replacement'] ) {
					continue;
				}

				$url = preg_replace( '/' . preg_quote( $alias['needle'], '/' ) . '/', $alias['replacement'], $url, 1 );
			}

			return $url;
		}

		public function parse_aliases() {

			if ( ! $this->url_aliases ) {
				return;
			}

			$site_path    = jet_smart_filters()->data->get_sitepath();
			$replaced_url = substr( $_SERVER['REQUEST_URI'], strlen( $site_path ) );

			foreach ( $this->url_aliases as $alias ) {
				if ( ! $alias['needle'] || ! $alias['replacement'] ) {
					continue;
				}

				$replaced_url = preg_replace( '/' . preg_quote( $alias['replacement'], '/' ) . '/', $alias['needle'], $replaced_url, 1 );
			}

			$replaced_url = $site_path . $replaced_url;

			foreach( array( '/jsf/', '?jsf=', '&jsf=' ) as $key ) {
				if ( stripos( $replaced_url, $key ) !== false ) {
					$_SERVER['REQUEST_URI'] = $replaced_url;
					$this->update_request_data_from_url( $replaced_url );

					remove_action( 'template_redirect', 'redirect_canonical' );

					break;
				}
			}
		}

		public function update_request_data_from_url( $url ) {

			$parts = parse_url( $url );

			if ( empty( $parts['query'] ) ) {
				return;
			}

			parse_str( $parts['query'], $params );

			foreach ( $params as $key => $param ) {
				if ( isset( $_REQUEST[$key] ) && $_REQUEST[$key] === $param ) {
					continue;
				}

				$_REQUEST[$key] = $param;
			}
		}
	}
}
