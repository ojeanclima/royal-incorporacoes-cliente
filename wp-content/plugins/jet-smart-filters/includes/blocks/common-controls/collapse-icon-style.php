<?php

$css_collapse_icon_scheme = apply_filters(
	'jet-smart-filters/widgets/collapse-icon/css-scheme',
	array(
		'collapse-icon' => '.jet-collapse-icon',
		'collapse-none' => '.jet-collapse-none',
	)
);

$this->controls_manager->start_section(
	'style_controls',
	[
		'id'          => 'collapse_icon_style_section',
		'initialOpen' => false,
		'title'       => esc_html__( 'Collapse icon', 'jet-smart-filters' ),
	]
);

$this->controls_manager->add_control([
	'id'        => 'collapse_icon_size',
	'type'      => 'range',
	'label'     => esc_html__( 'Size', 'jet-smart-filters' ),
	'separator' => 'after',
	'css_selector' => [
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'width: {{VALUE}}{{UNIT}}; height: {{VALUE}}{{UNIT}};',
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-none'] => 'width: {{VALUE}}{{UNIT}}; height: {{VALUE}}{{UNIT}};',
	],
	'attributes' => [
		'default' => [
			'value' => 20,
			'unit' => 'px'
		]
	],
	'units' => [
		[
			'value' => 'px',
			'intervals' => [
				'step' => 1,
				'min'  => 10,
				'max'  => 50,
			]
		],
		[
			'value' => '%',
			'intervals' => [
				'step' => 1,
				'min'  => 1,
				'max'  => 100,
			]
		],
	],
]);

$this->controls_manager->add_control([
	'id'        => 'collapse_icon_color',
	'type'      => 'color-picker',
	'label'     => esc_html__( 'Color', 'jet-smart-filters' ),
	'separator' => 'after',
	'css_selector' => array(
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] . ' svg path' => 'stroke: {{VALUE}}',
	),
]);

$this->controls_manager->add_control([
	'id'        => 'collapse_icon_background_color',
	'type'      => 'color-picker',
	'label'     => esc_html__( 'Background Color', 'jet-smart-filters' ),
	'separator' => 'after',
	'css_selector' => array(
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'background-color: {{VALUE}}',
	),
]);

$this->controls_manager->add_control([
	'id'        => 'collapse_icon_border',
	'type'      => 'border',
	'label'     => esc_html__( 'Border', 'jet-smart-filters' ),
	'separator' => 'after',
	'css_selector'  => array(
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'border-style:{{STYLE}};border-width:{{WIDTH}};border-radius:{{RADIUS}};border-color:{{COLOR}};',
	),
]);

$this->controls_manager->add_control([
	'id'         => 'collapse_icon_padding',
	'type'       => 'dimensions',
	'label'      => esc_html__( 'Padding', 'jet-smart-filters' ),
	'units'      => array( 'px', '%' ),
	'css_selector'  => array(
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon'] => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
	),
	'separator'  => 'after',
]);

$this->controls_manager->add_control([
	'id'         => 'collapse_icon_margin',
	'type'       => 'dimensions',
	'label'      => esc_html__( 'Margin', 'jet-smart-filters' ),
	'units'      => array( 'px', '%' ),
	'css_selector'  => array(
		'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-icon']     => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			'{{WRAPPER}} ' . $css_collapse_icon_scheme['collapse-none'] => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
	),
	'separator'  => 'after',
]);

$this->controls_manager->end_section();