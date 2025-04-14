<?php

namespace Jet_Smart_Filters\Bricks_Views\Elements;

use Jet_Engine\Bricks_Views\Helpers\Options_Converter;
use Bricks\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Smart_Filters_Bricks_Hidden extends Jet_Smart_Filters_Bricks_Base {
	// Element properties
	public $category = 'jetsmartfilters'; // Use predefined element category 'general'
	public $name = 'jet-smart-filters-hidden'; // Make sure to prefix your elements
	public $icon = 'jet-smart-filters-icon-hidden'; // Themify icon font class
	public $scripts = [ 'JetSmartFiltersBricksInit' ]; // Script(s) run when element is rendered on frontend or updated in builder

	public $jet_element_render = 'hidden';

	// Return localised element label
	public function get_label() {

		return esc_html__( 'Hidden Filter', 'jet-smart-filters' );
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

		$provider_allowed = \Jet_Smart_Filters\Bricks_Views\Manager::get_allowed_providers();

		$this->register_jet_control(
			'content_provider',
			[
				'tab'        => 'content',
				'label'      => esc_html__( 'This filter for', 'jet-smart-filters' ),
				'type'       => 'select',
				'options'    => Options_Converter::filters_options_by_key( jet_smart_filters()->data->content_providers(), $provider_allowed ),
				'searchable' => true,
			]
		);

		$this->register_jet_control(
			'epro_posts_notice',
			[
				'tab'      => 'content',
				'label'    => esc_html__( 'Please set <b>jet-smart-filters</b> into Query ID option of Posts widget you want to filter', 'jet-smart-filters' ),
				'type'     => 'info',
				'required' => [ 'content_provider', '=', [ 'epro-posts', 'epro-portfolio' ] ],
			]
		);

		$this->register_jet_control(
			'apply_type',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Apply type', 'jet-smart-filters' ),
				'type'    => 'select',
				'options' => [
					'ajax'   => esc_html__( 'AJAX', 'jet-smart-filters' ),
					'mixed'  => esc_html__( 'Mixed', 'jet-smart-filters' ),
				],
				'default' => 'ajax',
			]
		);

		$this->register_jet_control(
			'argument_type',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Argument Type', 'jet-smart-filters' ),
				'type'    => 'select',
				'options' => [
					'plain' => esc_html__( 'Plain', 'jet-smart-filters' ),
					'tax'   => esc_html__( 'Taxonomy', 'jet-smart-filters' ),
					'meta'  => esc_html__( 'Meta', 'jet-smart-filters' ),
				],
				'default' => 'plain',
			]
		);

		$this->register_jet_control(
			'argument_name',
			[
				'tab'            => 'content',
				'label'          => esc_html__( 'Name', 'jet-smart-filters' ),
				'type'           => 'text',
				'hasDynamicData' => false,
				'default'        => ''
			]
		);

		$this->register_jet_control(
			'argument_value',
			[
				'tab'            => 'content',
				'label'          => esc_html__( 'Value', 'jet-smart-filters' ),
				'type'           => 'text',
				'hasDynamicData' => true,
				'default'        => ''
			]
		);

		$this->register_jet_control(
			'query_id',
			[
				'tab'            => 'content',
				'label'          => esc_html__( 'Query ID', 'jet-smart-filters' ),
				'type'           => 'text',
				'hasDynamicData' => false,
				'description'    => esc_html__( 'Set unique query ID if you use multiple widgets of same provider on the page. Same ID you need to set for filtered widget.', 'jet-smart-filters' ),
			]
		);

		$this->end_jet_control_group();
	}

	// Render element HTML
	public function render() {

		jet_smart_filters()->set_filters_used();

		$settings = $this->get_jet_settings();
		$provider = ! empty( $settings['content_provider'] ) ? $settings['content_provider'] : '';

		// STEP: Content provider is empty: Show placeholder text
		if ( empty( $provider ) ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'Please select content provider to show.', 'jet-smart-filters' )
				]
			);
		}

		$is_editor          = ! $this->is_frontend;
		$hidden_filter_type = jet_smart_filters()->filter_types->get_filter_types( 'hidden' );
		$data_atts          = $hidden_filter_type->data_atts( $settings );

		echo "<div {$this->render_attributes( '_root' )}>";

		printf( '<div class="%1$s jet-filter">', $this->name );

		include jet_smart_filters()->get_template( 'filters/hidden.php' );

		echo '</div>';

		echo "</div>";
	}
}