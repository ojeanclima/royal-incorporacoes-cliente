<?php
namespace Jet_Engine_Dynamic_Tables\Blocks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Table_Block extends \Jet_Engine_Blocks_Views_Type_Base {

	/**
	 * Returns block name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return 'dynamic-table';
	}

	/**
	 * Return attributes array
	 *
	 * @return array
	 */
	public function get_attributes() {
		return array(
			'table_id' => array(
				'type'    => 'string',
				'default' => '',
			),
			'thead' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'tfoot' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'scrollable' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'rewrite_query' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'rewrite_query_id' => array(
				'type'    => 'string',
				'default' => '',
			),
			// Element ID
			'_element_id' => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	public function css_selector( $el = '' ) {
			return sprintf( '{{WRAPPER}} .jet-%1$s%2$s', $this->get_name(), $el );
		}

	/**
	 * Add style block options
	 *
	 * @return boolean
	 */
	public function add_style_manager_options() {

		$this->controls_manager->start_section(
			'style_controls',
			[
				'id'           => 'section_table_style',
				'initial_open' => true,
				'title'        => esc_html__( 'General', 'jet-engine' )
			]
		);

		$this->controls_manager->add_responsive_control( [
			'id'        => 'table_width',
			'type'      => 'range',
			'label'     => esc_html__( 'Table Width', 'jet-engine' ),
			'css_selector' => [
				$this->css_selector() => 'width: {{VALUE}}{{UNIT}}; max-width: {{VALUE}}{{UNIT}}',
			],
			'attributes' => [
				'default' => [
					'value' => [
						'value' => 100,
						'unit' => '%'
					]
				]
			],
			'units' => [
				[
					'value' => '%',
					'intervals' => [
						'step' => 1,
						'min'  => 10,
						'max'  => 100,
					]
				],
				[
					'value' => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 100,
						'max'  => 2500,
					]
				],
			],
		] );

		$this->controls_manager->add_control([
			'id'        => 'table_alignment',
			'type'      => 'choose',
			'label'   => __( 'Table Alignment Inside Container', 'elementor' ),
			'separator'    => 'before',
			'options'   =>[
				'0 auto 0 0'    => [
					'shortcut' => esc_html__( 'Left', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignleft',
				],
				'0 auto'    => [
					'shortcut' => esc_html__( 'Center', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-aligncenter',
				],
				'0 0 0 auto'    => [
					'shortcut' => esc_html__( 'Right', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignright',
				],
			],
			'css_selector' => [
				$this->css_selector() => 'margin: {{VALUE}}',
			],
		]);

		$this->controls_manager->add_control([
			'id'         => 'table_border',
			'type'       => 'border',
			'separator'  => 'before',
			'disable_radius' => true,
			'label'       => esc_html__( 'Border', 'jet-smart-filters' ),
			'css_selector'  => array(
				$this->css_selector() => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
			),
		]);

		$this->controls_manager->end_section();

		$this->controls_manager->start_section(
			'style_controls',
			[
				'id'           => 'section_heading_style',
				'initial_open' => true,
				'title'        => esc_html__( 'Heading', 'jet-engine' )
			]
		);

		$this->controls_manager->add_control([
			'id'         => 'headers_typography',
			'type'       => 'typography',
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',
			],
		]);

		$this->controls_manager->add_control([
			'id'           => 'headers_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Color', 'jet-engine' ),
			'separator'    => 'before',
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'color: {{VALUE}}',
			),
		]);

		$this->controls_manager->add_control([
			'id'           => 'headers_bg_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Background Color', 'jet-engine' ),
			'separator'    => 'before',
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'background-color: {{VALUE}}',
			),
		]);

		$this->controls_manager->add_responsive_control([
			'id'           => 'headers_padding',
			'type'         => 'dimensions',
			'label'        => esc_html__( 'Padding', 'jet-smart-filters' ),
			'units'        => array( 'px', '%' ),
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			),
			'separator'    => 'before',
		]);

		$this->controls_manager->add_control([
			'id'         => 'headers_border',
			'type'       => 'border',
			'separator'  => 'before',
			'disable_radius' => true,
			'label'       => esc_html__( 'Border', 'jet-smart-filters' ),
			'css_selector'  => array(
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
			),
		]);

		$this->controls_manager->add_control([
			'id'        => 'headers_h_align',
			'type'      => 'choose',
			'label'   => __( 'Alignment', 'elementor' ),
			'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
			'separator'    => 'before',
			'options'   =>[
				'left'    => [
					'shortcut' => esc_html__( 'Left', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignleft',
				],
				'center'    => [
					'shortcut' => esc_html__( 'Center', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-aligncenter',
				],
				'right'    => [
					'shortcut' => esc_html__( 'Right', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignright',
				],
			],
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'text-align: {{VALUE}};',
			],
		]);

		$this->controls_manager->add_control([
			'id'        => 'headers_v_align',
			'type'      => 'choose',
			'label'   => __( 'Vertical Align', 'elementor' ),
			'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
			'separator'    => 'before',
			'options'   =>[
				'left'    => [
					'shortcut' => esc_html__( 'Left', 'jet-smart-filters' ),
					'icon'  => 'dashicons-arrow-up',
				],
				'center'    => [
					'shortcut' => esc_html__( 'Center', 'jet-smart-filters' ),
					'icon'  => 'dashicons-image-flip-vertical',
				],
				'right'    => [
					'shortcut' => esc_html__( 'Right', 'jet-smart-filters' ),
					'icon'  => 'dashicons-arrow-down',
				],
			],
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'text-align: {{VALUE}};',
			],
		]);

		$this->controls_manager->end_section();

		$this->controls_manager->start_section(
			'style_controls',
			[
				'id'           => 'section_body_style',
				'initial_open' => true,
				'title'        => esc_html__( 'Body', 'jet-engine' )
			]
		);

		$this->controls_manager->add_control([
			'id'         => 'body_typography',
			'type'       => 'typography',
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',
			],
		]);

		$this->controls_manager->add_control([
			'id'           => 'body_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Color', 'jet-engine' ),
			'separator'    => 'before',
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'color: {{VALUE}}',
			),
		]);

		$this->controls_manager->add_control([
			'id'           => 'body_bg_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Background Color', 'jet-engine' ),
			'separator'    => 'before',
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'background-color: {{VALUE}}',
			),
		]);

		$this->controls_manager->add_responsive_control([
			'id'           => 'body_padding',
			'type'         => 'dimensions',
			'label'        => esc_html__( 'Padding', 'jet-smart-filters' ),
			'units'        => array( 'px', '%' ),
			'css_selector' => array(
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			),
			'separator'    => 'before',
		]);

		$this->controls_manager->add_control([
			'id'         => 'body_border',
			'type'       => 'border',
			'separator'  => 'before',
			'disable_radius' => true,
			'label'       => esc_html__( 'Border', 'jet-smart-filters' ),
			'css_selector'  => array(
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
			),
		]);

		$this->controls_manager->add_control([
			'id'        => 'body_h_align',
			'type'      => 'choose',
			'label'   => __( 'Alignment', 'elementor' ),
			'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
			'separator'    => 'before',
			'options'   =>[
				'left'    => [
					'shortcut' => esc_html__( 'Left', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignleft',
				],
				'center'    => [
					'shortcut' => esc_html__( 'Center', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-aligncenter',
				],
				'right'    => [
					'shortcut' => esc_html__( 'Right', 'jet-smart-filters' ),
					'icon'  => 'dashicons-editor-alignright',
				],
			],
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'text-align: {{VALUE}};',
			],
		]);

		$this->controls_manager->add_control([
			'id'        => 'body_v_align',
			'type'      => 'choose',
			'label'   => __( 'Vertical Align', 'elementor' ),
			'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
			'separator'    => 'before',
			'options'   =>[
				'left'    => [
					'shortcut' => esc_html__( 'Left', 'jet-smart-filters' ),
					'icon'  => 'dashicons-arrow-up',
				],
				'center'    => [
					'shortcut' => esc_html__( 'Center', 'jet-smart-filters' ),
					'icon'  => 'dashicons-image-flip-vertical',
				],
				'right'    => [
					'shortcut' => esc_html__( 'Right', 'jet-smart-filters' ),
					'icon'  => 'dashicons-arrow-down',
				],
			],
			'css_selector' => [
				$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'text-align: {{VALUE}};',
			],
		]);

		$this->controls_manager->end_section();

	}

}
