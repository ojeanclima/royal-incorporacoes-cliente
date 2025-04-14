<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class CCT extends Post_Types {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::CCT_ID;
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		if ( ! empty( $data ) && ! jet_engine()->modules->is_module_active( 'custom-content-types' ) ) {

			jet_engine()->modules->activate_module( 'custom-content-types' );

			$module = jet_engine()->modules->get_module( 'custom-content-types' );
			$module->create_instance( jet_engine() );
			\Jet_Engine\Modules\Custom_Content_Types\Module::instance()->init();
		}

		foreach ( $data as $raw_cct ) {

			if ( ! empty( $raw_cct['supports'] ) ) {

				$fields = [];

				foreach ( $raw_cct['supports'] as $support ) {

					if ( 'editor' === $support ) {
						$support = 'content';
					}

					$field = [
						'name'  => $support,
						'title' => ucfirst( $support ),
					];

					switch ( $support ) {
						case 'content':
							$field['type'] = 'wysiwyg';
							break;

						case 'thumbnail':
							$field['type'] = 'media';
							break;

						default:
							$field['type'] = 'text';
							break;
					}

					$fields[] = $field;
				}

				if ( empty( $raw_cct['metaFields'] ) ) {
					$raw_cct['metaFields'] = [];
				}

				$raw_cct['metaFields'] = array_merge( $fields, $raw_cct['metaFields'] );
			}

			\Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->data->set_request( array(
				'name'        => $raw_cct['title'],
				'slug'        => $raw_cct['slug'],
				'args'        => [
					'position' => '-1',
					'icon'     => 'dashicons-media-text',
				],
				'meta_fields' => $this->get_prepared_meta_fields( $raw_cct ),
			) );

			if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {
				$cct_id = 1;
			} else {
				$cct_id = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->data->create_item( false );
			}

			add_filter( 'jet-engine/query-builder/data/allowed-query-types', [ $this, 'allow_cct_query' ] );

			$query_id = $this->create_query(
				$raw_cct['title'],
				'custom-content-type',
				[ 'content_type' => $raw_cct['slug'] ]
			);

			$listing = $this->create_listing( $query_id, $raw_cct['title'], $raw_cct );

			if ( $cct_id ) {
				$this->log_entity( [
					'id'         => $cct_id,
					'slug'       => $raw_cct['slug'],
					'name'       => $raw_cct['title'],
					'query_id'   => $query_id,
					'listing_id' => isset( $listing['id'] ) ? $listing['id'] : false,
					'lsting_url' => isset( $listing['url'] ) ? $listing['url'] : false,
				] );
			}

		}

		return $result;
	}

	/**
	 * Make sure that is 'custom-content-type' query type is allowed to create.
	 * Is actual for the cases when CCT module activated on model creation.
	 *
	 * @param  array  $query_types Allowed query types.
	 * @return array
	 */
	public function allow_cct_query( $query_types = [] ) {
		if ( ! in_array( 'custom-content-type', $query_types ) ) {
			$query_types[] = 'custom-content-type';
		}

		return $query_types;
	}

	/**
	 * Get default data to set as listing content for created item.
	 *
	 * @param  array  $item_data [description]
	 * @return [type]            [description]
	 */
	public function get_default_listing_data( $item_data = [] ) {

		$result = [];

		if ( ! empty( $item_data['metaFields'] ) ) {
			foreach ( $item_data['metaFields'] as $meta_field ) {

				if ( empty( $meta_field['type'] ) || 'relation' === $meta_field['type'] ) {
					continue;
				}

				$name = $item_data['slug'] . '__' . $meta_field['name'];

				switch ( $meta_field['type'] ) {
					case 'media':
						$result[] = [
							'type'     => 'jet-listing-dynamic-image',
							'settings' => [
								'dynamic_image_source' => $name,
								'custom_source'        => $name,
								'dynamic_image_size'   => 'thumbnail',
								'is_cct_media'         => true,
							],
						];
						break;

					case 'repeater':

						$result[] = [
							'type'     => 'jet-listing-dynamic-repeater',
							'settings' => [
								'dynamic_field_source' => $name,
								'dynamic_field_format' => $this->get_repeater_field_format_for_listing( $meta_field ),
								'dynamic_field_before' => '<div>',
								'dynamic_field_after'  => '</div>'
							],
						];
						break;

					default:

						$settings = [
							'dynamic_field_source'      => 'object',
							'dynamic_field_post_object' => $name,
							'field_fallback'            => $meta_field['title'],
						];

						$result[] = [
							'type'     => 'jet-listing-dynamic-field',
							'settings' => $this->maybe_add_callback( $settings, $meta_field ),
						];
						break;
				}
			}
		}

		return $result;
	}
}
