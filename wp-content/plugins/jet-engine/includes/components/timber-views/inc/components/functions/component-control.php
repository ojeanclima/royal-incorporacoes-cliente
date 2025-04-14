<?php
/**
 * Base function class
 */
namespace Jet_Engine\Timber_Views\Components\Functions;

use Jet_Engine\Timber_Views\View\Functions\Base;

class Component_Control extends Base {

	public function get_name() {
		return 'jet_engine_component_control';
	}

	public function get_label() {
		return __( 'Component Control Value', 'jet-engine' );
	}

	public function get_result( $args ) {

		$control_name = ! empty( $args['name'] ) ? $args['name'] : false;
		$is_image = ! empty( $args['is_image'] ) ? filter_var( $args['is_image'], FILTER_VALIDATE_BOOLEAN ) : false;

		if ( ! $control_name ) {
			return;
		}

		$value = jet_engine()->listings->components->state->get( $control_name );

		if ( $is_image ) {
			return $this->get_image_control_value( $value, $args );
		}

		return wp_kses_post( $value );

	}

	public function get_image_control_value( $img_data = [], $args = [] ) {

		if ( empty( $img_data ) ) {
			return;
		}

		$return_as = ! empty( $args['return_as'] ) ? $args['return_as'] : 'url';
		$size      = ! empty( $args['size'] ) ? $args['size'] : 'full';

		// If is image ID
		if ( is_int( $img_data ) ) {
			if ( 'image' === $return_as ) {
				return wp_get_attachment_image( $img_data, $size );
			} else {
				return wp_get_attachment_image_url( $img_data, $size );
			}
		}

		// if is image URL
		if ( false !== filter_var( $img_data, FILTER_VALIDATE_URL ) ) {
			if ( 'image' === $return_as ) {
				return sprintf( '<img src="%1$s" alt="">', esc_url( $img_data ) );
			} else {
				return esc_url( $img_data );
			}
		}

		// If is image array
		if ( is_array( $img_data ) && ! empty( $img_data['id'] ) ) {
			if ( 'image' === $return_as ) {
				return wp_get_attachment_image( $img_data['id'], $size );
			} else {
				return wp_get_attachment_image_url( $img_data['id'], $size );
			}
		}
	}

	public function get_args() {

		return [
			'name' => [
				'label'       => __( 'Control Name', 'jet-engine' ),
				'type'        => 'text',
				'default'     => '',
			],
			'is_image' => [
				'label'       => __( 'Is Image', 'jet-engine' ),
				'type'        => 'switcher',
				'default'     => '',
			],
			'return_as' => [
				'label'       => __( 'Return result as', 'jet-engine' ),
				'type'        => 'select',
				'default'     => 'url',
				'options'     => [
					[
						'value' => 'url',
						'label' => __( 'Image URL', 'jet-engine' ),
					],
					[
						'value' => 'image',
						'label' => __( 'Image HTML Tag', 'jet-engine' ),
					]
				],
				'condition' => [
					'is_image' => true,
				],
			],
			'size' => [
				'label'   => __( 'Size', 'jet-engine' ),
				'type'    => 'select',
				'default' => 'full',
				'options' => jet_engine()->listings->get_image_sizes( 'blocks' ),
				'condition' => [
					'is_image' => true,
				],
			],
		];
	}

}
