<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Post_Types extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::POST_TYPES_ID;
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		foreach ( $data as $raw_post_type ) {

			jet_engine()->cpt->data->set_request( array(
				'name'                  => $raw_post_type['title'],
				'slug'                  => $raw_post_type['slug'],
				'custom_storage'        => ! empty( $raw_post_type['customStorage'] ) ? filter_var( $raw_post_type['customStorage'], FILTER_VALIDATE_BOOLEAN ) : false,
				'menu_name'             => $raw_post_type['title'],
				'name_admin_bar'        => $raw_post_type['title'],
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'show_in_nav_menus'     => true,
				'show_in_rest'          => true,
				'query_var'             => true,
				'rewrite'               => true,
				'has_archive'           => true,
				'menu_icon'             => 'dashicons-media-text',
				'supports'              => $raw_post_type['supports'],
				'menu_position'         => '-1',
				'admin_columns'         => [],
				'admin_filters'         => [],
				'meta_fields'           => $this->get_prepared_meta_fields( $raw_post_type ),
			) );

			if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {
				$post_type_id = 1;
			} else {
				$post_type_id = jet_engine()->cpt->data->create_item( false );
			}

			$query_id = $this->create_query(
				$raw_post_type['title'],
				'posts',
				[ 'post_type' => [ $raw_post_type['slug'] ] ]
			);

			$listing = $this->create_listing( $query_id, $raw_post_type['title'], $raw_post_type );

			if ( $post_type_id ) {
				$this->log_entity( [
					'id'         => $post_type_id,
					'slug'       => $raw_post_type['slug'],
					'name'       => $raw_post_type['title'],
					'query_id'   => $query_id,
					'listing_id' => isset( $listing['id'] ) ? $listing['id'] : false,
					'lsting_url' => isset( $listing['url'] ) ? $listing['url'] : false,
				] );
			}
		}

		return $result;
	}

	/**
	 * Get default data to set as listing content for created item.
	 *
	 * @param  array  $item_data [description]
	 * @return [type]            [description]
	 */
	public function get_default_listing_data( $item_data = [] ) {

		$result = [];

		if ( ! empty( $item_data['supports'] ) ) {

			if ( in_array( 'thumbnail', $item_data['supports'] ) ) {
				$result[] = [
					'type'     => 'jet-listing-dynamic-image',
					'settings' => [
						'dynamic_image_source' => 'post_thumbnail',
						'dynamic_image_size'   => 'thumbnail',
					],
				];
			}

			if ( in_array( 'title', $item_data['supports'] ) ) {
				$result[] = [
					'type'     => 'jet-listing-dynamic-field',
					'settings' => [
						'dynamic_field_source'      => 'object',
						'dynamic_field_post_object' => 'post_title',
						'field_fallback'            => 'Post Title',
					],
				];
			}
		}

		if ( ! empty( $item_data['metaFields'] ) ) {
			foreach ( $item_data['metaFields'] as $meta_field ) {

				if ( empty( $meta_field['type'] ) || 'relation' === $meta_field['type'] ) {
					continue;
				}

				switch ( $meta_field['type'] ) {
					case 'media':

						$result[] = [
							'type'     => 'jet-listing-dynamic-image',
							'settings' => [
								'dynamic_image_source' => $meta_field['name'],
								'custom_source'        => $meta_field['name'],
								'dynamic_image_size'   => 'thumbnail',
							],
						];
						break;

					case 'repeater':
						$result[] = [
							'type'     => 'jet-listing-dynamic-repeater',
							'settings' => [
								'dynamic_field_source' => $meta_field['name'],
								'dynamic_field_format' => $this->get_repeater_field_format_for_listing( $meta_field ),
								'dynamic_field_before' => '<div>',
								'dynamic_field_after'  => '</div>'
							],
						];
						break;

					default:

						$settings = [
							'dynamic_field_source'    => 'meta',
							'dynamic_field_post_meta' => $meta_field['name'],
							'field_fallback'          => $meta_field['title'],
						];

						$result[] = [
							'type'     => 'jet-listing-dynamic-field',
							'settings' => $this->maybe_add_callback( $settings, $meta_field ),
						];
						break;
				}
			}
		}

		$result[] = [
			'type'     => 'jet-listing-dynamic-link',
			'settings' => [
				'dynamic_link_source' => '_permalink',
				'link_label'          => 'Read More',
			],
		];

		return $result;
	}

	/**
	 * Get HTML format of repeater item to use in Dynamic Repeater field of listing
	 *
	 * @param  array  $meta_field Repeater field arguments.
	 * @return string
	 */
	public function get_repeater_field_format_for_listing( $meta_field = [] ) {

		$format = '';

		if ( ! empty( $meta_field['fields'] ) ) {
			foreach ( $meta_field['fields'] as $sub_field ) {

				$name = $sub_field['name'];

				switch ( $sub_field['type'] ) {
					case 'media':
						$format .= "<div><img src='%" . $name . "|img_url_by_id(thumbnail)%' alt=''></div>";
						break;

					case 'checkbox':
						$format .= "<div>%" . $name . "|render_checkbox%</div>";
						break;

					case 'date':
					case 'datetime':
					case 'datetime-local':
						$format .= "<div>%" . $name . "|format_date%</div>";
						break;

					default:
						$format .= '<div>%' . $name . '%</div>';
						break;
				}
			}
		}

		return $format;
	}

	/**
	 * Prepare meta field options
	 *
	 * @param  array  $field [description]
	 * @return [type]        [description]
	 */
	public function prepare_bulk_options( $field = [] ) {

		$options = [];

		if ( ! empty( $field['options'] ) ) {
			foreach ( $field['options'] as $option ) {
				$options[] = "{$option}::{$option}";
			}
		}

		return implode( "\r\n", $options );
	}

	/**
	 * Extract list of prepared meta fields from a post type array
	 *
	 * @param  array $post_type Post type data.
	 * @return array
	 */
	public function get_prepared_meta_fields( $post_type = [] ) {

		$meta_fields = [];

		if ( ! empty( $post_type['metaFields'] ) ) {
			foreach ( $post_type['metaFields'] as $meta_field ) {

				$field = $this->prepare_meta_field( $meta_field );

				if ( ! empty( $field ) ) {
					$meta_fields[] = $field;
				}
			}
		}

		return $meta_fields;
	}

	/**
	 * Prepare meta field to store as JetEngine meta field.
	 *
	 * @param  array $meta_field Raw field data.
	 * @param  bool  $nested     Is nested or top-level field.
	 * @return array|bool
	 */
	public function prepare_meta_field( $meta_field = [], $nested = false ) {

		if ( empty( $meta_field['type'] ) || 'relation' === $meta_field['type'] ) {
			return false;
		}

		if ( $nested && 'repeater' === $meta_field['type'] ) {
			return;
		}

		$field = [
			'title'       => $meta_field['title'],
			'name'        => $meta_field['name'],
			'object_type' => 'field',
			'width'       => '100%',
			'type'        => $meta_field['type'],
			'id'          => rand( 10000, 99999 ),
			'isNested'    => false, // defines nesting in UI tabs, accordion, not the nesting into the repeater.
			'options'     => [],
		];

		switch ( $meta_field['type'] ) {

			case 'media':
				$field['value_format'] = 'id';
				break;

			case 'repeater':

				$repeater_fields = [];

				if ( ! empty( $meta_field['fields'] ) ) {
					foreach ( $meta_field['fields'] as $raw_repeater_field ) {

						$repeater_field = $this->prepare_meta_field( $raw_repeater_field );

						if ( ! empty( $repeater_field ) ) {
							$repeater_fields[] = $repeater_field;
						}
					}
				}

				$field['repeater-fields'] = $repeater_fields;
				break;

			case 'select':
			case 'checkbox':
			case 'radio':
				$field['options_source'] = 'manual_bulk';
				$field['bulk_options']   = $this->prepare_bulk_options( $meta_field );
				$field['is_array']       = true;
				break;

			case 'date':
			case 'time':
			case 'datetime':
			case 'datetime-local':

				$field['type']         = $meta_field['type'];
				$field['input_type']   = $meta_field['type'];
				$field['autocomplete'] = 'off';
				$field['is_timestamp'] = true;

				break;
		}

		return $field;
	}
}
