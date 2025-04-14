<?php
/**
 * Workflows UI
 */
namespace Croblock\Workflows;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Remote_API {

	protected $api_url = 'https://api.crocoblock.com/interactive-tutorials/tutorials.json';

	/**
	 * Set API url for current instance
	 */
	public function set_api_url( $api_url = '' ) {
		$this->api_url = $api_url;
	}

	/**
	 * Remote retrieve items
	 * 
	 * @return [type] [description]
	 */
	public function get_items() {

		$workflows = [];
		$response  = wp_remote_get( $this->api_url );

		if ( ! $response || is_wp_error( $response ) ) {
			return $workflows;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body ) {
			return $workflows;
		}

		$workflows = json_decode( $body, true );

		return $workflows;

	}

}
