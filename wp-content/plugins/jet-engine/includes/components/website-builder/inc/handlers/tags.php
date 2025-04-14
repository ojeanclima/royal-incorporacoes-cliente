<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Tags extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::TAGS_ID;
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		foreach ( $data as $tag ) {
			$this->log_entity( [
				'tag' => $tag,
			] );
		}

		return $result;
	}
}
