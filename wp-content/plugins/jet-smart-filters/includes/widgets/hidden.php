<?php

namespace Elementor;

use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography as Global_Typography;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Jet_Smart_Filters_Hidden_Widget extends Widget_Base {

	public function get_name() {

		return 'jet-smart-filters-hidden';
	}

	public function get_title() {

		return __( 'Hidden Filter', 'jet-smart-filters' );
	}

	public function get_icon() {

		return 'jet-smart-filters-icon-hidden';
	}

	public function get_help_url() {

		return jet_smart_filters()->widgets->prepare_help_url(
			'https://crocoblock.com/knowledge-base/jetsmartfilters/hidden-filter-overview/',
			$this->get_name()
		);
	}

	public function get_categories() {

		return array( jet_smart_filters()->widgets->get_category() );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => __( 'Content', 'jet-smart-filters' ),
			)
		);

		$this->add_control(
			'content_provider',
			array(
				'label'   => __( 'This filter for', 'jet-smart-filters' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => jet_smart_filters()->data->content_providers(),
			)
		);

		$this->add_control(
			'epro_posts_notice',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => __( 'Please set <b>jet-smart-filters</b> into Query ID option of Posts widget you want to filter', 'jet-smart-filters' ),
				'condition' => array(
					'content_provider' => array( 'epro-posts', 'epro-portfolio' ),
				),
			)
		);

		$this->add_control(
			'apply_type',
			array(
				'label'   => __( 'Apply type', 'jet-smart-filters' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ajax',
				'options' => array(
					'ajax'  => __( 'AJAX', 'jet-smart-filters' ),
					'mixed' => __( 'Mixed', 'jet-smart-filters' ),
				),
			)
		);

		$this->add_control(
			'argument_type',
			array(
				'label'   => __( 'Argument Type', 'jet-smart-filters' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'plain',
				'options' => array(
					'plain' => __( 'Plain', 'jet-smart-filters' ),
					'tax'   => __( 'Taxonomy', 'jet-smart-filters' ),
					'meta'  => __( 'Meta', 'jet-smart-filters' )
				),
			)
		);

		$this->add_control(
			'argument_name',
			array(
				'label'   => esc_html__( 'Name', 'jet-smart-filters' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ''
			)
		);

		$this->add_control(
			'argument_value',
			array(
				'label'   => esc_html__( 'Value', 'jet-smart-filters' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array(
					'active' => true
				),
				'default' => ''
			)
		);

		$this->add_control(
			'query_id',
			array(
				'label'       => esc_html__( 'Query ID', 'jet-smart-filters' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Set unique query ID if you use multiple widgets of same provider on the page. Same ID you need to set for filtered widget.', 'jet-smart-filters' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {

		jet_smart_filters()->set_filters_used();

		$is_editor          = is_admin();
		$base_class         = $this->get_name();
		$settings           = $this->get_settings();
		$hidden_filter_type = jet_smart_filters()->filter_types->get_filter_types( 'hidden' );
		$data_atts          = $hidden_filter_type->data_atts( $settings );

		printf( '<div class="%1$s jet-filter">', $base_class );

		include jet_smart_filters()->get_template( 'filters/hidden.php' );

		echo '</div>';
	}
}