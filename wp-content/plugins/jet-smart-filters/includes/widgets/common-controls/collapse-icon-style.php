<?php
namespace Elementor;

$css_collapse_icon_scheme = apply_filters(
	'jet-smart-filters/widgets/collapse-icon/css-scheme',
	array(
		'collapse-icon' => '.jet-collapse-icon',
		'collapse-none' => '.jet-collapse-none',
	)
);

$this->start_controls_section(
	'collapse_icon_style_section',
	[
		'label'      => esc_html__( 'Collapse icon', 'jet-smart-filters' ),
		'tab'        => Controls_Manager::TAB_STYLE,
	]
);

$this->add_responsive_control(
	'collapse_icon_size',
	array(
		'label'      => esc_html__( 'Size', 'jet-smart-filters' ),
		'type'       => Controls_Manager::SLIDER,
		'size_units' => array(
			'px',
			'%',
		),
		'range'      => array(
			'px' => array(
				'min' => 10,
				'max' => 50,
			),
			'%'  => array(
				'min' => 1,
				'max' => 100,
			),
		),
		'default'    => array(
			'size' => 20,
			'unit' => 'px',
		),
		'selectors'  => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-none'] => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
		),
	)
);

$this->add_control(
	'collapse_icon_color',
	array(
		'label'     => esc_html__( 'Color', 'jet-smart-filters' ),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] . ' svg path' => 'stroke: {{VALUE}}',
		),
	)
);

$this->add_control(
	'collapse_icon_background_color',
	array(
		'label'     => esc_html__( 'Background Color', 'jet-smart-filters' ),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'background-color: {{VALUE}}',
		),
	)
);

$this->add_group_control(
	Group_Control_Border::get_type(),
	array(
		'name'        => 'collapse_icon_border',
		'label'       => esc_html__( 'Border', 'jet-smart-filters' ),
		'placeholder' => '1px',
		'default'     => '1px',
		'selector'    => '{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'],
	)
);

$this->add_control(
	'collapse_icon_border_radius',
	array(
		'label'      => esc_html__( 'Border Radius', 'jet-smart-filters' ),
		'type'       => Controls_Manager::DIMENSIONS,
		'size_units' => array( 'px', '%' ),
		'selectors'  => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		),
	)
);

$this->add_responsive_control(
	'collapse_icon_padding',
	array(
		'label'      => esc_html__( 'Padding', 'jet-smart-filters' ),
		'type'       => Controls_Manager::DIMENSIONS,
		'size_units' => array( 'px', '%' ),
		'selectors'  => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		),
		'separator'  => 'before'
	)
);

$this->add_responsive_control(
	'collapse_icon_margin',
	array(
		'label'      => esc_html__( 'Margin', 'jet-smart-filters' ),
		'type'       => Controls_Manager::DIMENSIONS,
		'size_units' => array( 'px', '%' ),
		'selectors'  => array(
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-none'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		),
	)
);

$this->end_controls_section();