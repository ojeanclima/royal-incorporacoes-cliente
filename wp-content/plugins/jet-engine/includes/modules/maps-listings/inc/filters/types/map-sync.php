<?php
namespace Jet_Engine\Modules\Maps_Listings\Filters\Types;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define User_Geolocation class
 */
class Map_Sync extends \Jet_Smart_Filters_Filter_Base {

	/**
	 * Get provider ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'map-sync';
	}

	/**
	 * Get provider name
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Map Sync', 'jet-engine' );
	}

	/**
	 * Get provider wrapper selector
	 *
	 * @return string
	 */
	public function get_scripts() {
		return array( 'jet-maps-listings-map-sync' );
	}

	public function get_template( $args = array() ) {
		return jet_engine()->modules->modules_path( 'maps-listings/inc/filters/types/map-sync-template.php' );
	}

	/**
	 * Prepare filter template argumnets
	 *
	 * @param  [type] $args [description]
	 *
	 * @return [type]       [description]
	 */
	public function prepare_args( $args ) {

		$additional_providers = isset( $args['additional_providers'] ) ? $args['additional_providers'] : false;

		return array(
			'options'              => false,
			'query_type'           => 'map_sync',
			'query_var'            => '',
			'content_provider'     => 'jet-engine-maps',
			'additional_providers' => $additional_providers,
			'apply_type'           => 'ajax',
		);

	}

}
