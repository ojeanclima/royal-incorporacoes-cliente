<?php
namespace Jet_Engine\Blocks_Views\Components;

class Block_Type {

	protected $block_name;
	protected $block_title;

	protected $attributes = [];

	public function __construct( $component ) {

		$this->block_name  = 'jet-engine/' . $component->get_element_name();
		$this->block_title = $component->get_display_name();

		$this->setup_attributes( $component );

		register_block_type(
			$this->get_block_name(),
			apply_filters( 'jet-engine/blocks-views/components/component-block-args', [
				'title' => $this->get_block_title(),
				'attributes' => $this->get_attributes(),
				'render_callback' => function( $attributes, $content ) use ( $component ) {

					if ( ! empty( $attributes['component_context'] ) ) {
						$component->set_component_context( $attributes['component_context'] );
					}
					
					$content = $component->get_content( $attributes, false );

					$result = sprintf(
						'<div class="jet-listing-grid--%1$s" style="%2$s">%3$s</div>',
						$component->get_id(),
						$component->css_variables_string( $attributes ),
						$content
					);

					return $result;

				}
			], $component )
		);
	}

	/**
	 * Setup component attributes
	 * 
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function setup_attributes( $component ) {
		
		$attributes = [];

		$attributes['component_context'] = [
			'type'        => 'text',
			'default'     => 'default_object',
			'label'       => 'Component Context',
			'controlType' => [
				'label'		=> 'Component Context',
				'type'		=> 'select',
				'default'	=> 'default_object',
				'options' 	=> jet_engine()->listings->allowed_context_list( 'blocks' ),
			],
		];

		foreach ( $component->get_props() as $content_prop ) {

			$type = 'text';

			if ( ! isset( $content_prop['control_type'] ) ) {
				continue;
			}

			$control_type = $component->get_prop_for_control( $content_prop );

			if ( ! empty( $control_type['options'] ) ) {
				$control_type['options'] = \Jet_Engine_Tools::prepare_list_for_js(
					$control_type['options'],
					ARRAY_A 
				);
			}

			$default = $component->get_prop_default( $content_prop );

			if ( 'media' === $content_prop['control_type'] ) {
				$type    = 'object';
				$default = ! empty( $default ) ? $default : [ 'id' => false ];
			}

			$attributes[ $content_prop['control_name'] ] = [
				'type'        => $type,
				'default'     => $default,
				'label'       => $content_prop['control_label'],
				'controlType' => $control_type,
			];
		}

		foreach ( $component->get_styles() as $style_prop ) {

			if ( ! empty( $style_prop['control_name'] ) ) {
				$attributes[ $style_prop['control_name'] ] = [
					'type' => 'text',
					'default' => isset( $style_prop['control_default'] ) ? $style_prop['control_default'] : '',
					'controlType' => [
						'label' => ! empty( $style_prop['control_label'] ) ? $style_prop['control_label'] : $style_prop['control_name'],
						'type'  => 'color',
					]
				];
			}

		}

		$this->attributes = $attributes;

	}

	/**
	 * Returns block name to register
	 * 
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function get_block_name() {
		return $this->block_name;
	}

	/**
	 * Returns block title to display
	 * 
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function get_block_title() {
		return $this->block_title;
	}

	/**
	 * Returns block attributes
	 * 
	 * @return [type] [description]
	 */
	public function get_attributes() {
		return $this->attributes;
	}

}
