<?php

namespace Jet_Engine\Modules\Maps_Listings\Filters\Bricks;

use Jet_Engine\Bricks_Views\Helpers\Options_Converter;
use Jet_Smart_Filters\Bricks_Views\Elements\Jet_Smart_Filters_Bricks_Base;
use Bricks\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Map_Sync extends Jet_Smart_Filters_Bricks_Base {
	// Element properties
	public $category = 'jetsmartfilters'; // Use predefined element category 'general'
	public $name = 'jet-smart-filters-map-sync'; // Make sure to prefix your elements
	public $icon = 'jet-engine-icon-map-synch'; // Themify icon font class
	public $scripts = [ 'jetEngineBricks' ]; // Script(s) run when element is rendered on frontend or updated in builder

	public $jet_element_render = 'map-sync';

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Map Sync', 'jet-engine' );
	}

	// Set builder control groups
	public function set_control_groups() {
		$this->register_general_group();
	}

	// Set builder controls
	public function set_controls() {
		$this->register_general_controls();
	}

	public function register_general_controls() {

		$this->start_jet_control_group( 'section_general' );

		$query_builder_link = admin_url( 'admin.php?page=jet-engine-query' );

		$this->register_jet_control(
			'query_notice',
			[
				'content' => sprintf( __( 'This filter is compatible only with queries from <a href="%s" target="_blank">JetEngine Query Builder</a>. ALso you need to set up <a href="https://crocoblock.com/knowledge-base/jetengine/how-to-set-geo-search-based-on-user-geolocation/" target="_blank">Geo Query</a> in your query settings to make the filter work correctly.', 'jet-engine' ), $query_builder_link ),
				'type' => 'info',
			]
		);

		// $this->register_jet_control(
		// 	'map_listing_id',
		// 	[
		// 		'tab'            => 'content',
		// 		'label'          => esc_html__( 'Map Listing ID', 'jet-smart-filters' ),
		// 		'type'           => 'text',
		// 		'hasDynamicData' => false,
		// 		'description'    => esc_html__( 'CSS ID of Map Listing that triggers filtering. If left empty, the first Map Listing on the page will be taken.', 'jet-smart-filters' ),
		// 	]
		// );

		$provider_allowed = \Jet_Smart_Filters\Bricks_Views\Manager::get_allowed_providers();

		$query_providers = array(
			'jet-engine-calendar' => true,
			'jet-engine'          => true,
			'jet-engine-maps'     => true,
			'bricks-query-loop'   => true,
			'jet-data-table'      => true,
			'jet-data-chart'      => true,
		);

		$query_providers = apply_filters( 'jet-engine/maps-listing/map-sync/allowed-providers/bricks', $query_providers );

		$provider_allowed = array_intersect_key( $provider_allowed, $query_providers );

		// $this->register_jet_control(
		// 	'content_provider',
		// 	[
		// 		'tab'        => 'content',
		// 		'label'      => esc_html__( 'This filter for', 'jet-smart-filters' ),
		// 		'type'       => 'select',
		// 		'options'    => Options_Converter::filters_options_by_key( jet_smart_filters()->data->content_providers(), $provider_allowed ),
		// 		'searchable' => true,
		// 	]
		// );

		$this->register_jet_control(
			'content_provider',
			[
				'tab'            => 'content',
				'content'        => sprintf(
					'<b>%s</b><br><br><i>%s</i>',
					__( 'This filter for: Map Listing.', 'jet-smart-filters' ),
					__( 'Map Sync filter does not work if there is no Map Listing on the page, and that Map Listing is a main provider.', 'jet-smart-filters' )
				),
				'type'           => 'info',
			]
		);

		$this->register_jet_control(
			'query_id',
			[
				'tab'            => 'content',
				'label'          => esc_html__( 'Query ID', 'jet-smart-filters' ),
				'type'           => 'text',
				'hasDynamicData' => false,
				'description'    => esc_html__( 'Map Listing query ID. Set unique query ID if you use multiple Map Listings on the page. Same ID you need to set for filtered widget.', 'jet-smart-filters' ),
			]
		);

		$this->register_jet_control(
			'additional_providers_enabled',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Additional providers enabled', 'jet-smart-filters' ),
				'type'    => 'checkbox',
				'default' => false,
			]
		);

		$repeater = new \Jet_Engine\Bricks_Views\Helpers\Repeater();

		$repeater->add_control(
			'additional_provider_notice_cache_query_loop',
			[
				'tab'         => 'content',
				'type'        => 'info',
				'content'     => esc_html__( 'You have enabled the "Cache query loop" option.', 'jet-smart-filters' ),
				'description' => sprintf(
					esc_html__( 'This option will break the filters functionality. You can disable this option or use "JetEngine Query Builder" query type. Go to: %s > Cache query loop', 'jet-smart-filters' ),
					'<a href="' . Helpers::settings_url( '#tab-performance' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'jet-smart-filters' ) . ' > Performance</a>'
				),
				'required'    => [
					[ 'additional_provider', '=', 'bricks-query-loop' ],
					[ 'cacheQueryLoops', '=', true, 'globalSettings' ],
				],
			]
		);

		$repeater->add_control(
			'additional_provider',
			[
				'label'      => esc_html__( 'Additional provider', 'jet-smart-filters' ),
				'type'       => 'select',
				'options'    => Options_Converter::filters_options_by_key( jet_smart_filters()->data->content_providers(), $provider_allowed ),
				'searchable' => true,
			]
		);

		$repeater->add_control(
			'additional_query_id',
			[
				'label' => esc_html__( 'Additional query ID', 'jet-smart-filters' ),
				'type'  => 'text',
			]
		);

		$this->register_jet_control(
			'additional_providers_list',
			[
				'tab'           => 'content',
				'label'         => esc_html__( 'Additional providers list', 'jet-smart-filters' ),
				'type'          => 'repeater',
				'titleProperty' => 'additional_provider',
				'fields'        => $repeater->get_controls(),
				'required'      => [ 'additional_providers_enabled', '=', true ],
			]
		);

		$this->end_jet_control_group();
	}

	// Render element HTML
	public function render() {
		jet_smart_filters()->set_filters_used();

		$settings = $this->parse_jet_render_attributes( $this->get_jet_settings() );
		$settings['filter_id'] = 0;
		$settings['additional_providers'] = jet_smart_filters()->utils->get_additional_providers( $settings );

		echo "<div {$this->render_attributes( '_root' )}>";
		jet_smart_filters()->filter_types->render_filter_template( 'map-sync', $settings );
		echo "</div>";
	}

}
