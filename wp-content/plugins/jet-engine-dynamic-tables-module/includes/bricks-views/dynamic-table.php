<?php

namespace Jet_Engine_Dynamic_Tables\Bricks_Views;

use Jet_Engine\Bricks_Views\Elements\Base;
use Jet_Engine_Dynamic_Tables\Plugin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Dynamic_Table extends Base {
	// Element properties
	public $category = 'jetengine'; // Use predefined element category 'general'
	public $name = 'jet-dynamic-table'; // Make sure to prefix your elements
	public $icon = 'jet-engine-icon-dynamic-table'; // Themify icon font class
	public $css_selector = '.jet-dynamic-table'; // Default CSS selector
	public $scripts = [ 'jetEngineBricks' ]; // Script(s) run when element is rendered on frontend or updated in builder

	public $jet_element_render = 'dynamic-table';

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Dynamic Table', 'jet-engine' );
	}

	// Set builder control groups
	public function set_control_groups() {

		$this->register_jet_control_group(
			'section_general',
			[
				'title' => esc_html__( 'General', 'jet-engine' ),
				'tab'   => 'content',
			]
		);

		$this->register_jet_control_group(
			'section_table_style',
			[
				'title' => esc_html__( 'Table base', 'jet-engine' ),
				'tab'   => 'style',
			]
		);

		$this->register_jet_control_group(
			'section_heading_style',
			[
				'title' => esc_html__( 'Table Headers', 'jet-engine' ),
				'tab'   => 'style',
			]
		);

		$this->register_jet_control_group(
			'section_body_style',
			[
				'title' => esc_html__( 'Table Body', 'jet-engine' ),
				'tab'   => 'style',
			]
		);

	}

	// Set builder controls
	public function set_controls() {

		$this->start_jet_control_group( 'section_general' );

		$this->register_jet_control(
			'table_id',
			[
				'tab'         => 'content',
				'label'       => esc_html__( 'Table', 'jet-engine' ),
				'type'        => 'select',
				'options'     => Plugin::instance()->data->get_tables_for_options( 'elementor' ),
				'searchable'  => true,
				'description' => esc_html__( 'Select table to show', 'jet-engine' ),
			]
		);

		$this->register_jet_control(
			'thead',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Show column names in table header', 'jet-engine' ),
				'type'    => 'checkbox',
				'default' => true,
			]
		);

		$this->register_jet_control(
			'tfoot',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Show column names in table footer', 'jet-engine' ),
				'type'    => 'checkbox',
				'default' => false,
			]
		);

		$this->register_jet_control(
			'scrollable',
			[
				'tab'     => 'content',
				'label'   => esc_html__( 'Allow horizontal scroll', 'jet-engine' ),
				'type'    => 'checkbox',
				'default' => false,
			]
		);

		$this->register_jet_control(
			'rewrite_query',
			[
				'tab'         => 'content',
				'label'       => esc_html__( 'Rewrite table query', 'jet-engine' ),
				'type'        => 'checkbox',
				'default'     => false,
				'description' => esc_html__( 'Use different query. Allow to use different data for same layout and avoid tables duplicating', 'jet-engine' ),
			]
		);

		$this->register_jet_control(
			'rewrite_query_id',
			[
				'tab'      => 'content',
				'label'    => esc_html__( 'New Query', 'jet-engine' ),
				'type'     => 'select',
				'options'  => \Jet_Engine\Query_Builder\Manager::instance()->get_queries_for_options(),
				'required' => [ 'rewrite_query', '=', true ],
			]
		);


		$this->end_jet_control_group();

		$this->start_jet_control_group( 'section_table_style' );

		$this->register_jet_control(
			'table_collapse',
			[
				'tab'     => 'style',
				'label'   => esc_html__( 'Layout', 'jet-engine' ),
				'type'    => 'select',
				'options' => [
					'separate' => esc_html__( 'Separate', 'jet-engine' ),
					'collapse' => esc_html__( 'Collapse', 'jet-engine' ),
				],
				'css'     => [
					[
						'property' => 'border-collapse',
					],
				],
				'default' => 'separate',
			]
		);

		$this->register_jet_control(
			'table_border_spacing',
			[
				'tab'      => 'style',
				'label'    => esc_html__( 'Border spacing', 'jet-engine' ),
				'type'     => 'number',
				'units'    => true,
				'css'      => [
					[
						'property' => 'border-spacing',
					],
				],
				'required' => [ 'table_collapse', '=', 'separate' ],
			]
		);

		$this->end_jet_control_group();

		$this->start_jet_control_group( 'section_heading_style' );

		$this->register_jet_control(
			'headers_typography',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Typography', 'jet-engine' ),
				'type'  => 'typography',
				'css'   => [
					[
						'property' => 'typography',
						'selector' => $this->css_selector( '__header ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'headers_bg_color',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Background', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'property' => 'background-color',
						'selector' => $this->css_selector( '__header ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'headers_padding',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Padding', 'jet-engine' ),
				'type'  => 'dimensions',
				'css'   => [
					[
						'property' => 'padding',
						'selector' => $this->css_selector( '__header ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'headers_border',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Border', 'jet-engine' ),
				'type'  => 'border',
				'css'   => [
					[
						'property' => 'border',
						'selector' => $this->css_selector( '__header ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'headers_v_align',
			[
				'tab'     => 'style',
				'label'   => esc_html__( 'Vertical align', 'jet-engine' ),
				'type'    => 'select',
				'options' => array(
					'top'    => 'Top',
					'middle' => 'Middle',
					'bottom' => 'Bottom',
				),
				'css'     => [
					[
						'property' => 'vertical-align',
						'selector' => $this->css_selector( '__header ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->end_jet_control_group();

		$this->start_jet_control_group( 'section_body_style' );

		$this->register_jet_control(
			'body_bg_color',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Background', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'property' => 'background-color',
						'selector' => $this->css_selector( '__body ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'body_padding',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Padding', 'jet-engine' ),
				'type'  => 'dimensions',
				'css'   => [
					[
						'property' => 'padding',
						'selector' => $this->css_selector( '__body ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'body_border',
			[
				'tab'   => 'style',
				'label' => esc_html__( 'Border', 'jet-engine' ),
				'type'  => 'border',
				'css'   => [
					[
						'property' => 'border',
						'selector' => $this->css_selector( '__body ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->register_jet_control(
			'body_v_align',
			[
				'tab'     => 'style',
				'label'   => esc_html__( 'Vertical align', 'jet-engine' ),
				'type'    => 'select',
				'options' => array(
					'top'    => 'Top',
					'middle' => 'Middle',
					'bottom' => 'Bottom',
				),
				'css'     => [
					[
						'property' => 'vertical-align',
						'selector' => $this->css_selector( '__body ' ) . $this->css_selector( '__col' ),
					],
				],
			]
		);

		$this->end_jet_control_group();
	}

	// Enqueue element styles and scripts
	public function enqueue_scripts() {
		wp_enqueue_style( 'jet-engine-frontend' );
	}

	// Render element HTML
	public function render() {

		// STEP: Table field is empty: Show placeholder text
		if ( empty( $this->get_jet_settings( 'table_id' ) ) ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'Please select table to show.', 'jet-engine' )
				]
			);
		}

		$this->set_attribute( '_root', 'class', 'jet-listing-base' );
		$this->enqueue_scripts();

		$render = $this->get_jet_render_instance();

		// STEP: Dynamic Table renderer class not found: Show placeholder text
		if ( ! $render ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'Dynamic Table renderer class not found', 'jet-engine' )
				]
			);
		}

		echo "<div {$this->render_attributes( '_root' )}>";
		$render->render_content();
		echo "</div>";

	}

	public function parse_jet_render_attributes( $attrs = [] ) {

		$attrs['thead'] = $attrs['thead'] ?? false;

		return $attrs;
	}

	public function css_selector( $mod = null ) {
		return sprintf( '%1$s%2$s', $this->css_selector, $mod );
	}
}