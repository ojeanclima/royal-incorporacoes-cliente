<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Bricks_Views\Dynamic_Data;

use Jet_Engine\Modules\Custom_Content_Types\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Provider_Content_Types extends \Bricks\Integrations\Dynamic_Data\Providers\Base {

	public function register_tags() {

		$fields = self::get_fields();

		foreach ( $fields as $field ) {
			$this->register_tag( $field );
		}
	}

	public function register_tag( $field ) {
		$name = "je_cct_{$field['_brx_object']}_{$field['name']}";
		$label = $field['title'];

		$tag = [
			'name'     => '{' . $name . '}',
			'label'    => $label,
			'group'    => "Jet Engine CCT ({$field['_brx_group_label']})",
			'field'    => $field,
			'provider' => $this->name
		];

		// Repeater field (loop)
		if ( in_array( $field['type'], [ 'repeater', 'posts' ] ) ) {

			// Add the 'posts' field to both loop and regular fields lists
			if ( $field['type'] === 'posts' ) {
				$this->tags[ $name ] = $tag;
			}

			$tag['label'] = 'JE ' . ucfirst( $field['type'] ) . ': ' . $label;

			$this->loop_tags[ $name ] = $tag;

			if ( ! empty( $field['repeater-fields'] ) ) {
				foreach ( $field['repeater-fields'] as $sub_field ) {

					$sub_field['_brx_type']        = $field['_brx_type'];
					$sub_field['_brx_object']      = $field['_brx_object'];
					$sub_field['_brx_group_label'] = $field['_brx_group_label'];

					$this->register_tag( $sub_field, $field ); // Recursive
				}
			}
		}

		// Regular fields
		else {
			$this->tags[ $name ] = $tag;
		}
	}

	public static function get_fields() {
		$fields   = [];
		$supports = self::get_supported_field_types();

		// STEP: CCT meta fields
		$content_types = Module::instance()->manager->get_content_types();

		if ( empty( $content_types ) ) {
			return $fields;
		}

		foreach ( $content_types as $type => $instance ) {
			$register_hidden_fields = apply_filters( 'jet-engine/bricks-views/dynamic-data/show-hidden-fields', true, $type );

			foreach ( $instance->get_formatted_fields() as $field ) {
				if ( ( $field['object_type'] !== 'field' || ! in_array( $field['type'], $supports ) ) && ! $register_hidden_fields ) {
					continue;
				}

				$field['_brx_type']        = 'post';
				$field['_brx_object']      = $type; // object slug
				$field['_brx_group_label'] = $instance->get_arg( 'name' );

				$fields[] = $field;
			}
		}

		return $fields;
	}

	public function get_tag_value( $tag, $post, $args, $context ) {
		$post    = jet_engine()->listings->data->get_current_object();
		$post_id = isset( $post->_ID ) ? $post->_ID : '';

		$field = $this->tags[ $tag ]['field'];

		// STEP: Check for filter args
		$filters = $this->get_filters_from_args( $args );

		// STEP: Get the value
		$je_cct_field = "{$field['_brx_object']}__{$field['name']}";
		$value = jet_engine()->listings->data->get_prop( $je_cct_field );

		// @since 1.8 - New array_val filter. Once used, we don't want to process the field type logic
		if ( isset( $filters['array_value'] ) && is_array( $value ) ) {
			// Force context to text
			$context = 'text';
			$value   = $this->return_array_value( $value, $filters );
		}

		// Process field type logic
		else {
			switch ( $field['type'] ) {
				case 'date':
					if ( ! empty( $value ) ) {
						if ( isset( $field['is_timestamp'] ) && ! $field['is_timestamp'] ) {
							// The value is a date string, change to timestamp
							$value = strtotime( $value );
						}

						$filters['object_type'] = 'date';
					}
					break;

				case 'datetime-local':
					if ( ! empty( $value ) ) {
						if ( isset( $field['is_timestamp'] ) && ! $field['is_timestamp'] ) {
							// The value is a date string, change to timestamp
							$value = strtotime( $value );
						}

						$filters['object_type'] = 'datetime';
					}
					break;

				case 'time':
					if ( ! empty( $value ) ) {
						// The value is always a string in 24-hour format, convert to timestamp
						$value = strtotime( $value );

						if ( empty( $filters['meta_key'] ) ) {
							// If no meta_key is set, we force the meta_key format so Bricks :time filter can work
							$filters['meta_key'] = 'H:i';
						}

						$filters['object_type'] = 'datetime';
					}
					break;

				case 'media':
					$filters['object_type'] = 'media';
					$filters['separator']   = '';

					if ( isset( $field['value_format'] ) ) {
						if ( $field['value_format'] === 'url' ) {
							$value = attachment_url_to_postid( $value );
						} elseif ( $field['value_format'] === 'both' ) {
							$value = isset( $value['id'] ) ? $value['id'] : '';
						}
					}

					// Empty field value should return empty array to avoid default post title in text context. @see $this->format_value_for_context()
					$value = ! empty( $value ) ? [ $value ] : [];

					break;

				case 'gallery':
					$filters['object_type'] = 'media';
					$filters['separator']   = ', ';

					if ( isset( $filters['image'] ) ) {
						$filters['separator']   = '';
					}

					if ( isset( $field['value_format'] ) ) {
						if ( $field['value_format'] === 'id' ) {
							$value = explode( ',', $value );
						} elseif ( $field['value_format'] === 'url' ) {
							$value = explode( ',', $value );
							$value = array_map( 'attachment_url_to_postid', $value );
							$value = array_filter( $value );
						} elseif ( $field['value_format'] === 'both' ) {
							$value = wp_list_pluck( $value, 'id' );
						}
					} else {
						// Empty field value should return empty array to avoid default post title in text context. @see $this->format_value_for_context()
						$value = ! empty( $value ) ? explode( ',', $value ) : [];
					}

					break;

				case 'posts':
					$filters['object_type'] = 'post';
					$filters['link']        = true;

					break;

				case 'checkbox':
					$options_source = $field['options_source'] ?? '';

					if ( $options_source === 'glossary' ) {
						$glossary_id = $field['glossary_id'] ?? 0;
						$value       = jet_engine_label_by_glossary( $value, $glossary_id );
					} else {
						$value = jet_engine_render_checkbox_values( $value );
					}

					break;
			}
		}

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;
	}

	/**
	 * Get all fields supported
	 *
	 * @return array
	 */
	private static function get_supported_field_types() {
		return [
			'text',
			'textarea',
			'wysiwyg',
			'number',
			'html',

			'date',
			'time',
			'datetime-local',

			'switcher',
			'checkbox',
			'radio',
			'select',

			// 'iconpicker',
			'media',
			'gallery',

			'repeater', // Query Loop

			'posts', // Query Loop (and regular field)

			'colorpicker',
		];
	}
}
