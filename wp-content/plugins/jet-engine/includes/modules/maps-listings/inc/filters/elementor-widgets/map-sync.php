<?php
namespace Jet_Engine\Modules\Maps_Listings\Filters\Elementor_Widgets;

use \Elementor\Controls_Manager;
use \Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Map_Sync extends \Elementor\Jet_Smart_Filters_Base_Widget {

	public function get_name() {
		return 'jet-smart-filters-map-sync';
	}

	public function get_title() {
		return __( 'Map Sync', 'jet-engine' );
	}

	public function get_icon() {
		return 'jet-engine-icon-map-synch';
	}

	public function get_help_url() {}

	public function content_providers() {
		$allowed = array(
			'jet-engine-calendar',
			'jet-engine',
			'jet-woo-products-grid',
			'jet-woo-products-list',
			'jet-engine-maps',
			'bricks-query-loop',
			'jet-data-table',
			'jet-data-chart',
		);

		$allowed = apply_filters( 'jet-engine/maps-listing/map-sync/allowed-providers/elementor', $allowed );

		$all = jet_smart_filters()->data->content_providers();

		$result = array();

		foreach ( $allowed as $name ) {
			if ( ! isset( $all[ $name ] ) ) {
				continue;
			}

			$result[ $name ] = $all[ $name ];
		}

		return $result;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => __( 'Content', 'jet-smart-filters' ),
			)
		);

		$query_builder_link = admin_url( 'admin.php?page=jet-engine-query' );

		$this->add_control(
			'query_notice',
			array(
				'label' => '',
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( '<b>Please note!</b><br><div class="elementor-control-field-description">This filter is compatible only with queries from <a href="%s" target="_blank">JetEngine Query Builder</a>. ALso you need to set up <a href="https://crocoblock.com/knowledge-base/jetengine/how-to-set-geo-search-based-on-user-geolocation/" target="_blank">Geo Query</a> in your query settings to make the filter work correctly.</div>', 'jet-engine' ), $query_builder_link ),
			)
		);

		// $this->add_control(
		// 	'map_listing_id',
		// 	array(
		// 		'label'       => esc_html__( 'Map Listing ID', 'jet-smart-filters' ),
		// 		'type'        => Controls_Manager::TEXT,
		// 		'label_block' => true,
		// 		'description' => __( 'CSS ID of Map Listing that triggers filtering. If left empty, the first Map Listing on the page will be taken.', 'jet-smart-filters' ),
		// 	)
		// );

		$this->add_control(
			'content_provider',
			array(
				'label'   => '',
				'type'    => Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					'<b>%s</b><br><br><i>%s</i>',
					__( 'This filter for: Map Listing.', 'jet-smart-filters' ),
					__( 'Map Sync filter does not work if there is no Map Listing on the page, and that Map Listing is a main provider.', 'jet-smart-filters' )
				),
				// 'options' => $this->content_providers(),
			)
		);

		$this->add_control(
			'apply_type',
			array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'ajax',
			)
		);
		
		$this->add_control(
			'apply_on',
			array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'value',
			)
		);

		// $this->add_control(
		// 	'epro_posts_notice',
		// 	array(
		// 		'type' => Controls_Manager::RAW_HTML,
		// 		'raw'  => __( 'Please set <b>jet-smart-filters</b> into Query ID option of Posts widget you want to filter', 'jet-smart-filters' ),
		// 		'condition' => array(
		// 			'content_provider' => array( 'epro-posts', 'epro-portfolio' ),
		// 		),
		// 	)
		// );

		$this->add_control(
			'query_id',
			array(
				'label'       => esc_html__( 'Query ID', 'jet-smart-filters' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Map Listing query ID. Set unique query ID if you use multiple Map Listings on the page. Same ID you need to set for filtered widget.', 'jet-smart-filters' ),
			)
		);

		$this->add_control(
			'additional_providers_enabled',
			array(
				'label'        => esc_html__( 'Additional Providers Enabled', 'jet-smart-filters' ),
				'type'         => Controls_Manager::SWITCHER,
				'description'  => '',
				'label_on'     => esc_html__( 'Yes', 'jet-smart-filters' ),
				'label_off'    => esc_html__( 'No', 'jet-smart-filters' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);
		
		$repeater = new Repeater();
		
		$repeater->add_control(
			'additional_provider',
			array(
				'label'       => __( 'Additional Provider', 'jet-smart-filters' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->content_providers(),
			)
		);
		
		$repeater->add_control(
			'additional_query_id',
			array(
				'label'       => esc_html__( 'Additional Query ID', 'jet-smart-filters' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
			)
		);
		
		$this->add_control(
			'additional_providers_list',
			array(
				'label' => __( 'Additional Providers List', 'jet-smart-filters' ),
				'type'  => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{ additional_provider + ( additional_query_id ? "/" + additional_query_id : "" ) }}',
				'condition'   => array(
					'additional_providers_enabled' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->register_filter_settings_controls();

	}

	protected function render() {
		jet_smart_filters()->set_filters_used();

		$this->add_render_attribute(
			'_wrapper',
			array(
				'style' => 'display: none;',
			)
		);

		$args = $this->get_settings();

		$args['filter_id'] = 0;

		$args['additional_providers'] = jet_smart_filters()->utils->get_additional_providers( $args );

		jet_smart_filters()->filter_types->render_filter_template( $this->get_widget_fiter_type(), $args );
	}

}
