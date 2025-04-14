<?php
namespace Jet_Engine\Bricks_Views\Components;

use \Jet_Engine\Listings\Components\Component;

class Provider_Component_Prop extends \Bricks\Integrations\Dynamic_Data\Providers\Base {

	public function register_tags() {

		$fields = self::get_fields();

		foreach ( $fields as $field ) {
			$this->register_tag( $field );
		}
	}

	public function register_tag( $field ) {

		$suffix = '';

		if ( 'color' === $field['type'] ) {
			$suffix = '___color';
		}

		$name  = 'jet_component__' . $field['name'] . $suffix;
		$label = $field['title'];

		$tag = [
			'name'     => '{' . $name . '}',
			'label'    => $label,
			'group'    => 'Jet Engine Components (' . $field['_brx_group_label'] . ')',
			'field'    => $field,
			'provider' => $this->name
		];

		$this->tags[ $name ] = $tag;
	}

	public static function get_fields() {
		
		$fields     = [];
		$components = jet_engine()->listings->components->registry->get_components();

		if ( empty( $components ) ) {
			return $fields;
		}

		foreach ( $components as $component ) {

			foreach ( $component->get_props() as $prop ) {

				$fields[] = [
					'name' => $prop['control_name'],
					'title' => $prop['control_label'] . ' (' . esc_html__( 'Content Prop', 'jet-engine' ) . ')',
					'type' => $prop['control_type'],
					'_brx_group_label' => $component->get_display_name(),
				];
			}

			foreach ( $component->get_styles() as $prop ) {

				$fields[] = [
					'name' => $prop['control_name'],
					'title' => $prop['control_label'] . ' (' . esc_html__( 'Style Prop', 'jet-engine' ) . ')',
					'type' => 'color',
					'_brx_group_label' => $component->get_display_name(),
				];
			}
		}

		return $fields;
	}

	public function get_tag_value( $tag, $post, $args, $context ) {

		$post_type = get_post_type( $post );

		if ( isset( $post->ID ) && jet_engine()->post_type->slug() === $post_type ) {
			$preview = new \Jet_Engine_Listings_Preview( [], $post->ID );
			$post = $preview->get_preview_object();
		}

		$post_id = isset( $post->_ID ) ? $post->_ID : '';
		$field = $this->tags[ $tag ]['field'];

		// STEP: Check for filter args
		$filters = $this->get_filters_from_args( $args );

		// STEP: Get the value
		$value = jet_engine()->listings->components->state->get( $field['name'] );

		// @since 1.8 - New array_val filter. Once used, we don't want to process the field type logic
		if ( isset( $filters['array_value'] ) && is_array( $value ) ) {
			// Force context to text
			$context = 'text';
			$value   = $this->return_array_value( $value, $filters );
		}
		// Process field type logic
		else {
			switch ( $field['type'] ) {

				case 'media':

					$filters['object_type'] = 'media';
					$filters['separator']   = '';

					// Empty field value should return empty array to avoid default post title in text context. @see $this->format_value_for_context()
					if ( ! empty( $value ) ) {
						if ( is_array( $value ) && isset( $value['id'] ) ) {
							$value = [ $value['id'] ];
						} elseif ( is_array( $value ) && isset( $value['useDynamicData'] ) ) {
							$value = $value['useDynamicData'];
						} else {
							$value = [ $value ];
						}
					} else {
						$value = [];
					}

					break;

				case 'color':

					$value = sprintf( 'var(%1$s%2$s)', Component::css_var_prefix(), $field['name'] );

					break;

			}
		}

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;

	}

}
