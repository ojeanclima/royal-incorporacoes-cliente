<?php
namespace Jet_Engine_Dynamic_Tables\Elementor;

use Jet_Engine_Dynamic_Tables\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Table_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'jet-dynamic-table';
	}

	public function get_title() {
		return __( 'Dynamic Table', 'jet-engine' );
	}

	public function get_icon() {
		return 'jet-engine-icon-dynamic-table';
	}

	public function get_categories() {
		return array( 'jet-listing-elements' );
	}

	public function get_help_url() {
		return 'https://crocoblock.com/knowledge-base/article-category/jetbooking/';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => __( 'Content', 'jet-engine' ),
			)
		);

		$this->add_control(
			'table_id',
			array(
				'label'       => esc_html__( 'Table', 'jet-engine' ),
				'description' => esc_html__( 'Select table to show', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => Plugin::instance()->data->get_tables_for_options( 'elementor' ),
			)
		);

		$this->add_control(
			'thead',
			array(
				'label'       => esc_html__( 'Show column names in table header', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'yes',
			)
		);

		$this->add_control(
			'tfoot',
			array(
				'label'       => esc_html__( 'Show column names in table footer', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);

		$this->add_control(
			'scrollable',
			array(
				'label'       => esc_html__( 'Allow horizontal scroll', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);

		$this->add_control(
			'rewrite_query',
			array(
				'label'       => __( 'Rewrite table query', 'jet-engine' ),
				'description' => __( 'Use different query. Allow to use different data for same layout and avoid tables duplicating', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);

		$this->add_control(
				'rewrite_query_id',
				array(
					'label'   => __( 'New Query', 'jet-engine' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => \Jet_Engine\Query_Builder\Manager::instance()->get_queries_for_options(),
					'condition' => array(
						'rewrite_query' => 'yes',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_table_style',
			array(
				'label'      => __( 'General', 'jet-engine' ),
				'tab'        => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'table_width',
			array(
				'label'      => __( 'Table width', 'jet-engine' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px' ),
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => array(
						'min' => 100,
						'max' => 2500,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					$this->css_selector() => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'table_alignment',
			array(
				'label'   => __( 'Table Alignment', 'elementor' ),
				'description' => __( 'How table should be align inside container', 'jet-engine' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => '',
				'classes' => 'elementor-control-align',
				'options' => array(
					'0 auto 0 0' => array(
						'title' => __( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					),
					'0 auto' => array(
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					),
					'0 0 0 auto' => array(
						'title' => __( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					$this->css_selector() => 'margin: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'           => 'table_border',
				'label'          => __( 'Border', 'jet-engine' ),
				'placeholder'    => '1px',
				'selector'       => $this->css_selector(),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'table_box_shadow',
				'selector' =>  $this->css_selector(),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_heading_style',
			array(
				'label'      => __( 'Headers', 'jet-engine' ),
				'tab'        => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'headers_typography',
				'selector' => $this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ),
			)
		);

		$this->add_control(
			'headers_color',
			array(
				'label' => __( 'Color', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'headers_bg_color',
			array(
				'label' => __( 'Background', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'headers_padding',
			array(
				'label'      => __( 'Cells Padding', 'jet-engine' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'        => 'headers_border',
				'label'       => __( 'Border', 'jet-engine' ),
				'placeholder' => '1px',
				'selector'    => $this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ),
			)
		);

		$this->add_responsive_control(
			'headers_h_align',
			array(
				'label'   => __( 'Alignment', 'elementor' ),
				'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => '',
				'classes' => 'elementor-control-align',
				'options' => array(
					'left' => array(
						'title' => __( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => __( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'text-align: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'headers_v_align',
			array(
				'label' => __( 'Vertical Align', 'elementor' ),
				'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'top' => array(
						'title' => __( 'Top', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					),
				),
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__header .jet-dynamic-table__col' ) => 'vertical-align: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_body_style',
			array(
				'label'      => __( 'Body', 'jet-engine' ),
				'tab'        => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'body_typography',
				'selector' => $this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ),
			)
		);

		$this->add_control(
			'body_color',
			array(
				'label' => __( 'Color', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'body_bg_color',
			array(
				'label' => __( 'Background', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'body_padding',
			array(
				'label'      => __( 'Cells Padding', 'jet-engine' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'        => 'body_border',
				'label'       => __( 'Border', 'jet-engine' ),
				'placeholder' => '1px',
				'selector'    => $this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ),
			)
		);

		$this->add_responsive_control(
			'body_h_align',
			array(
				'label'   => __( 'Alignment', 'elementor' ),
				'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => '',
				'classes' => 'elementor-control-align',
				'options' => array(
					'left' => array(
						'title' => __( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => __( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'text-align: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'body_v_align',
			array(
				'label' => __( 'Vertical Align', 'elementor' ),
				'description' => __( 'These settings may be overriden for each column at the table edit page', 'jet-engine' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'top' => array(
						'title' => __( 'Top', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					),
				),
				'selectors' => array(
					$this->css_selector( ' .jet-dynamic-table__body .jet-dynamic-table__col' ) => 'vertical-align: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Returns CSS selector for nested element
	 *
	 * @param  [type] $el [description]
	 * @return [type]     [description]
	 */
	public function css_selector( $el = null ) {
		return sprintf( '{{WRAPPER}} .%1$s%2$s', $this->get_name(), $el );
	}

	protected function render() {
		jet_engine()->listings->render_item( 'dynamic-table', $this->get_settings() );
	}

}
