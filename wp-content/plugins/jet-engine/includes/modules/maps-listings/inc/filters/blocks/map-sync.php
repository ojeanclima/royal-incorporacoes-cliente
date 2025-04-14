<?php
namespace Jet_Engine\Modules\Maps_Listings\Filters\Blocks;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Map_Sync class
 */
class Map_Sync extends \Jet_Smart_Filters_Block_Base {

	/**
	 * Returns block name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'map-sync';
	}

	/**
	 * Return attributes array
	 */
	public function get_attributes() {
		return array(
			'content_provider' => array(
				'type'    => 'string',
				'default' => 'not-selected',
			),
			'query_id' => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	public function render_callback( $settings = array() ) {
		jet_smart_filters()->set_filters_used();
		$settings['filter_id'] = 0;
		$settings['additional_providers'] = jet_smart_filters()->utils->get_additional_providers( $settings );

		ob_start();
		jet_smart_filters()->filter_types->render_filter_template( 'map-sync', $settings );
		return ob_get_clean();
	}

}
