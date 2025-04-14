<?php
namespace Jet_Engine\Elementor_Views\Components;

use Elementor\Controls_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Base_Widget extends \Elementor\Widget_Base {

	protected $jet_engine_component;
	protected $jet_instance_id;

	public function __construct( $data = [], $args = null, $component = null ) {

		if ( ! $component && isset( $data['widgetType'] ) ) {
			$component = jet_engine()->listings->components->get( $data['widgetType'] );
		}

		$this->set_jet_engine_component( $component );

		parent::__construct( $data, $args );

	}

	public function set_jet_engine_component( $component ) {
		$this->jet_engine_component = $component;
	}

	public function get_name() {
		return $this->jet_engine_component->get_element_name();
	}

	public function get_title() {
		return $this->jet_engine_component->get_display_name();
	}

	public function get_icon() {
		return 'jet-engine-icon-component';
	}

	public function get_categories() {
		return [ $this->jet_engine_component->get_category() ];
	}

	public function get_keywords() {
		return [ $this->jet_engine_component->get_display_name(), 'component', 'JetEngine' ];
	}

	/**
	 * Map abstract names of componets props types to specific Elementor control name
	 *
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function control_types_map() {

		return [
			'textarea' => Controls_Manager::TEXTAREA,
			'rich_text' => Controls_Manager::WYSIWYG,
			'select' => Controls_Manager::SELECT,
			'media' => Controls_Manager::MEDIA,
			'media_gallery' => Controls_Manager::GALLERY,
			'icon' => Controls_Manager::ICONS,
		];

	}

	protected function register_controls() {

		$props  = $this->jet_engine_component->get_props();
		$styles = $this->jet_engine_component->get_styles();

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'elementor-addon' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$context_options = [
			'label'       => __( 'Component Context', 'jet-engine' ),
			'description' => __( 'Set context to use for the dynamic data inside current component', 'jet-engine' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'default_object',
			'label_block' => true,
			'options'     => jet_engine()->listings->allowed_context_list(),

		];

		if ( ! empty( $props ) ) {
			$context_options['separator'] = 'after';
		}

		$this->add_control( 'component_context', $context_options );

		if ( ! empty( $props ) ) {

			foreach ( $props as $prop ) {

				$this->add_control(
					$prop['control_name'],
					array_merge(
						[
							'label_block' => true,
							'dynamic'     => [
								'active' => true,
								'categories' => [
									\Jet_Engine_Dynamic_Tags_Module::TEXT_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::JET_MACROS_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::NUMBER_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::IMAGE_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::URL_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::POST_META_CATEGORY,
									\Jet_Engine_Dynamic_Tags_Module::COLOR_CATEGORY,
								],
							],
						],
						$this->jet_engine_component->get_prop_for_control( $prop, $this->control_types_map() )
					)
				);
			}

		}

		$this->end_controls_section();

		if ( ! empty( $styles ) ) {

			$this->start_controls_section(
				'section_styles',
				[
					'label' => esc_html__( 'Styles', 'elementor-addon' ),
					'tab' => Controls_Manager::TAB_CONTENT,
				]
			);

			foreach ( $styles as $style ) {

				if ( empty( $style['control_name'] ) ) {
					continue;
				}

				$control_data = [
					'label'       => ! empty( $style['control_label'] ) ? $style['control_label'] : $style['control_name'],
					'type'        => Controls_Manager::COLOR,
					'render_type' => 'template',
					'default'     => '',
				];

				$style = $this->adjust_raw_css_var_to_global( $style );

				// If we have default global color from Elementor
				if ( ! empty( $style['__globals__'] ) ) {
					$control_data['global'] = [
						'default' => $style['__globals__']['control_default'],
					];
				}

				if ( ! empty( $style['control_default'] ) ) {
					$control_data['default'] = $style['control_default'];
				}

				$this->add_control( $style['control_name'], $control_data );
			}

			$this->end_controls_section();

		}

	}

	/**
	 * Check if we have directly set Elementor global variable into default value
	 * and extract is as __globals__ attribute is yes
	 *
	 * @param  array $style Default style control data
	 * @return array
	 */
	protected function adjust_raw_css_var_to_global( $style ) {

		if ( ! empty( $style['control_default'] ) && false !== strpos( $style['control_default'], '--e-global-color' ) ) {

			$default_value = rtrim( ltrim( $style['control_default'], 'var( ' ), ' )' );
			$default_value = str_replace( '--e-global-color-', '', $default_value );
			$style['__globals__'] = [
				'control_default' => 'globals/colors?id=accent'
			];

			unset( $style['control_default'] );

		}

		return $style;

	}

	/**
	 * Get unique intstance ID for given component
	 *
	 * @return [type] [description]
	 */
	public function get_jet_instance_id() {

		if ( ! $this->jet_instance_id ) {
			$this->jet_instance_id = rand( 1000, 9999 );
		}

		return $this->jet_instance_id;
	}

	/**
	 * Returns unique classname for current component instance
	 * @return [type] [description]
	 */
	public function get_jet_component_instance_class() {


		return 'jet-component-instance-' . $this->get_jet_instance_id();
	}

	/**
	 * Apply unique class to component dynamic styles selectors
	 *
	 * @param  [type] $selector [description]
	 * @return [type]           [description]
	 */
	public function apply_component_selector( $selector ) {
		return '.' . $this->get_jet_component_instance_class() . '.jet-listing-grid--' . $this->jet_engine_component->get_id();
	}

	public function apply_unique_css_id( $unique_id ) {
		return $this->get_jet_instance_id();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->jet_engine_component->set_component_context( $this->get_settings( 'component_context' ) );

		add_filter(
			'jet-engine/elementor-views/dynamic-tags/dynamic-css-unique-id',
			[ $this, 'apply_unique_css_id' ]
		);

		add_filter(
			'jet-engine/elementor-views/dynamic-css/unique-listing-selector',
			[ $this, 'apply_component_selector' ]
		);

		$base_class   = 'jet-listing-grid--' . $this->jet_engine_component->get_id();
		$unique_class = $this->get_jet_component_instance_class();
		$classes      = [ $base_class, $unique_class ];

		$settings['component_unique_class'] = $unique_class;

		$content = $this->jet_engine_component->get_content( $settings, false );

		remove_filter(
			'jet-engine/elementor-views/dynamic-tags/dynamic-css-unique-id',
			[ $this, 'apply_unique_css_id' ]
		);

		remove_filter(
			'jet-engine/elementor-views/dynamic-css/unique-listing-selector',
			[ $this, 'apply_component_selector' ]
		);

		// If is 'elementor' component - we can just echo content,
		// for other views we need some additional moves to ensure CSS controls correctly processed
		if ( 'elementor' === $this->jet_engine_component->get_render_view() ) {
			echo $content;
		} else {

			if ( ! empty( $settings['__globals__'] ) ) {
				$settings = array_merge( $settings, Document::get_global_values( [
					'control_type' => 'color',
				], $settings['__globals__'] ) );
			}

			printf(
				'<div class="%1$s" style="%2$s">%3$s</div>',
				implode( ' ', $classes ),
				$this->jet_engine_component->css_variables_string( $settings ),
				$content
			);

		}

	}
}
