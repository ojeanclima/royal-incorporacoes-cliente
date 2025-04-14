<?php

namespace Jet_Engine\Modules\Custom_Content_Types\Bricks_Views;

use Jet_Engine\Modules\Custom_Content_Types\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Manager {
	public function __construct() {
		if ( ! $this->has_bricks() ) {
			return;
		}

		add_filter( 'jet-engine/bricks-views/dynamic_data/register_providers', array( $this, 'add_dynamic_data_provider' ) );
		add_filter( 'bricks/query/loop_object_id', array( $this, 'set_loop_object_id' ), 10, 2 );
	}

	/**
	 * Register Dynamic Data providers for CCT
	 *
	 * @param array $providers List of registered providers
	 */
	public function add_dynamic_data_provider( $providers ) {
		require Module::instance()->module_path( 'bricks-views/dynamic-data/provider.php' );
		$providers['content-types'] = '\Jet_Engine\Modules\Custom_Content_Types\Bricks_Views\Dynamic_Data';

		return $providers;
	}

	/**
	 * Set loop object id for generating dynamic css in Listing grid
	 *
	 * @param int    $object_id The original object ID.
	 * @param object $object    The object being checked.
	 * @return int The determined loop object ID.
	 */
	public function set_loop_object_id( $object_id, $object ) {
		if ( $object instanceof \stdClass && isset( $object->cct_slug ) && isset( $object->_ID ) ) {
			return $object->_ID;
		}

		return $object_id;
	}

	public function has_bricks() {
		return ( defined( 'BRICKS_VERSION' ) && \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_bricks_views' ) );
	}
}