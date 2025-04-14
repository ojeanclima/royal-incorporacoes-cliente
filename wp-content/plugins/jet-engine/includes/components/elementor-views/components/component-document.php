<?php
namespace Jet_Engine\Elementor_Views\Components;

use \Elementor\Controls_Manager;
use \Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Document extends \Jet_Listing_Item_Document {

	public $jet_component = null;
	public $jet_component_props = null;
	public $jet_styles_props = [];

	public function get_name() {
		return jet_engine()->listings->components->get_component_base_name();
	}

	public static function get_title() {
		return __( 'Cоmponent', 'jet-engine' );
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['admin_tab_group'] = '';
		$properties['support_kit']     = true;
		$properties['cpt']             = [ jet_engine()->listings->post_type->slug() ];

		return $properties;
	}

	public function get_css_wrapper_selector() {
		return '.jet-listing-item.single-jet-engine.elementor-page-' . $this->get_main_id();
	}

	public function register_jet_controls() {

		$this->start_controls_section(
			'jet_component_settings',
			array(
				'label' => __( 'Component Content Controls', 'jet-engine' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'control_label',
			array(
				'label'       => __( 'Control Label', 'jet-engine' ),
				'description' => __( 'Control label to show in the component UI in editor', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'control_name',
			[
				'label'       => __( 'Control Name', 'jet-engine' ),
				'description' => __( 'Control key/name to save into the DB. Please use only lowercase letters, numbers and "_". Also please note - name must be unique for this component (for both - styles and controls)', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'control_type',
			array(
				'label'       => __( 'Control Type', 'jet-engine' ),
				'description' => __( 'Type of control for UI', 'jet-engine' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'text',
				'options'     => jet_engine()->listings->components->get_supported_control_types(),
			)
		);

		$repeater->add_control(
			'control_options',
			array(
				'label'       => __( 'Options', 'jet-engine' ),
				'description' => __( 'One option per line. Split label and value with "::", for example - red::Red', 'jet-engine' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'condition'   => [
					'control_type' => [ 'select' ],
				],
			)
		);

		$repeater->add_control(
			'control_default',
			array(
				'label'       => __( 'Default Value', 'jet-engine' ),
				'description' => __( 'Default value of the given control', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => [
					'control_type!' => [ 'media', 'media_gallery', 'icon' ],
				],
			)
		);

		$repeater->add_control(
			'control_default_image',
			array(
				'label'       => __( 'Default Value', 'jet-engine' ),
				'description' => __( 'Default value of the given control', 'jet-engine' ),
				'type'        => Controls_Manager::MEDIA,
				'label_block' => true,
				'condition'   => [
					'control_type' => [ 'media' ],
				],
			)
		);

		$repeater->add_control(
			'control_default_icon',
			array(
				'label'       => __( 'Default Value', 'jet-engine' ),
				'description' => __( 'Default value of the given control', 'jet-engine' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'condition'   => [
					'control_type' => [ 'icon' ],
				],
			)
		);

		$this->add_control(
			'component_controls_list',
			array(
				'label'         => __( 'Component Controls', 'jet-engine' ),
				'type'          => 'jet-repeater',
				'fields'        => $repeater->get_controls(),
				'prevent_empty' => false,
				'item_actions' => [
					'add' => true,
					'duplicate' => false,
					'remove' => true,
					'sort' => true,
				],
				'default'       => [
					[
						'_id'                   => 'default_example',
						'control_label'         => 'Text Control',
						'control_name'          => 'text_control',
						'control_type'          => 'text',
						'control_options'       => '',
						'control_default'       => 'Default Value',
						'control_default_image' => false,
						'control_default_icon'  => false,
					]
				],
				'title_field' => '{{{ control_label }}}'
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'jet_component_styles',
			array(
				'label' => __( 'Component Style Controls', 'jet-engine' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'controls_style_notice',
			array(
				'heading'     => __( 'Please note:', 'jet-engine' ),
				'content'     => __( 'At the moment, only Color controls are supported for component styles', 'jet-engine' ),
				'type'        => Controls_Manager::NOTICE,
				'dismissible' => false,
			)
		);
		

		$repeater = new Repeater();

		$repeater->add_control(
			'control_label',
			array(
				'label'       => __( 'Control Label', 'jet-engine' ),
				'description' => __( 'Control label to show in the component UI in editor', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'control_name',
			[
				'label'       => __( 'Control Name', 'jet-engine' ),
				'description' => __( 'Control key/name to save into the DB. Please use only lowercase letters, numbers and "_". Also please note - name must be unique for this component (for both - styles and controls)', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'control_default',
			array(
				'label'       => __( 'Default Value', 'jet-engine' ),
				'description' => __( 'Default value of the given control', 'jet-engine' ),
				'type'        => Controls_Manager::COLOR,
			)
		);

		$this->add_control(
			'component_style_controls_list',
			array(
				'label'         => __( 'Style Controls', 'jet-engine' ),
				'type'          => 'jet-repeater',
				'fields'        => $repeater->get_controls(),
				'item_actions'  => [
					'add' => true,
					'duplicate' => false,
					'remove' => true,
					'sort' => true,
				],
				'prevent_empty' => false,
				'default'       => [],
				'title_field' => '{{{ control_label }}}'
			)
		);

		$this->end_controls_section();

	}

	public function get_component_for_document() {
		
		if ( null === $this->jet_component ) {
			$component_name = jet_engine()->listings->components->get_component_base_name() . '-' . $this->get_main_id();
			$this->jet_component = jet_engine()->listings->components->get( $component_name );
		}

		return $this->jet_component;
	}

	public function get_default_component_props() {

		if ( null === $this->jet_component_props ) {
			$props = [];
			$component_controls = $this->get_settings_for_display( 'component_controls_list' );

			$component = $this->get_component_for_document();

			if ( ! empty( $component_controls ) ) {
				foreach ( $component_controls as $control ) {
					$props[ $control['control_name'] ] = $component->get_prop_default( $control );
				}
			}

			$style_controls = $this->get_settings_for_display( 'component_style_controls_list' );

			if ( ! empty( $style_controls ) ) {
				foreach ( $style_controls as $control ) {
					
					if ( ! empty( $control['__globals__'] ) ) {
						$control = array_merge( $control, self::get_global_values( $control, $control['__globals__'] ) );
					}

					$default_value = $component->get_prop_default( $control );
					$props[ $control['control_name'] ] = $default_value;

					$this->jet_styles_props[ $control['control_name'] ] = $default_value;
				}
			}

			$this->jet_component_props = $props;
		}
		
		

		return $this->jet_component_props;
	}

	public static function get_global_values( $control = [], $global_settings = [] ) {

		$result = [];

		foreach ( $global_settings as $key => $value ) {
			$type = ! empty( $control['control_type'] ) ? $control['control_type'] : 'color';
			$global_data = explode( '?id=', $value );

			if ( ! empty( $global_data[1] ) ) {
				$id = $global_data[1];
				$result[ $key ] = "var( --e-global-$type-$id )";
			}

		}

		return $result;
	}

	public function get_preview_as_query_args() {

		jet_engine()->listings->components->state->set( $this->get_default_component_props() );

		return parent::get_preview_as_query_args();

	}

	public function get_container_attributes() {

		$attributes    = parent::get_container_attributes();
		$component     = $this->get_component_for_document();
		$state         = jet_engine()->listings->components->state->get();
		$default_props = $this->get_default_component_props();

		if ( empty( $state ) ) {
			jet_engine()->listings->components->state->set( $default_props );
			$state = jet_engine()->listings->components->state->get();
		} else {
			$state = $this->ensure_style_props( $state );
		}

		$attributes['style'] = $component->css_variables_string( $state );

		if ( empty( $attributes['class'] ) ) {
			$attributes['class'] = '';
		}

		$attributes['class'] .= sprintf( ' jet-listing-grid--%1$s', $component->get_id() );

		if ( ! empty( $state['component_unique_class'] ) ) {
			$attributes['class'] .= ' ' . $state['component_unique_class'];
		}

		return $attributes;

	}

	public function ensure_style_props( $state = [] ) {
		
		$globals = isset( $state['__globals__'] ) ? $this->get_global_values( [], $state['__globals__'] ) : [];

		foreach ( $this->jet_styles_props as $prop => $value ) {
			if ( empty( $state[ $prop ] ) ) {
				$state[ $prop ] = ! empty( $globals[ $prop ] ) ? $globals[ $prop ] : $value;
			}
		}

		return $state;
	}

}
