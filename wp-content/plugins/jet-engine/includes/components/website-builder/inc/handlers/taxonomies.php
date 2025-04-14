<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Taxonomies extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::TAXONOMIES_ID;
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;
		$data   = $this->merge_duplicated( $data );

		foreach ( $data as $raw_tax ) {

			$object_types = isset( $raw_tax['post_types'] ) ? $raw_tax['post_types'] : [ $raw_tax['post_type'] ];

			jet_engine()->taxonomies->data->set_request( array(
				'name'                       => $raw_tax['title'],
				'slug'                       => $raw_tax['name'],
				'object_type'                => $object_types,
				'menu_name'                  => $raw_tax['title'],
				'all_items'                  => $raw_tax['title'],
				'public'                     => true,
				'publicly_queryable'         => true,
				'show_ui'                    => true,
				'show_in_menu'               => true,
				'show_in_nav_menus'          => true,
				'show_in_rest'               => true,
				'query_var'                  => true,
				'rewrite'                    => true,
				'hierarchical'               => true,
				'rewrite_slug'               => $raw_tax['name'],
				'meta_fields'                => [],
			) );

			if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {
				$tax_id = 1;
			} else {
				$tax_id = jet_engine()->taxonomies->data->create_item( false );
			}

			if ( $tax_id ) {

				$this->log_entity( [
					'id'   => $tax_id,
					'slug' => $raw_tax['name'],
					'name' => $raw_tax['title'],
				] );

				if ( ! empty( $raw_tax['sampleData'] ) ) {
					$this->create_sample_terms( $raw_tax['name'], $raw_tax['sampleData'] );
				}
			}
		}

		return $result;
	}

	/**
	 * Check if the same tax is registered for multiple post types and merge them into the one
	 *
	 * @return array
	 */
	public function merge_duplicated( $data = [] ) {

		$result = [];

		foreach ( $data as $tax ) {
			if ( isset( $result[ $tax['name'] ] ) ) {
				$result[ $tax['name'] ]['post_types'][] = $tax['post_type'];
			} else {
				$result[ $tax['name'] ] = $tax;
				$result[ $tax['name'] ]['post_types'] = [ $tax['post_type'] ];
			}
		}

		return array_values( $result );
	}

	/**
	 * Create sample terms of given taxonomy
	 *
	 * @return void
	 */
	public function create_sample_terms( $tax = '', $terms = [] ) {

		if ( $tax && ! empty( $terms ) ) {

			if ( ! taxonomy_exists( $tax ) ) {
				register_taxonomy( $tax, [ 'post' ], [
					'labels' => [ 'name' => $tax ],
					'hierarchical' => true,
				] );
			}

			foreach ( $terms as $term ) {
				$term = ucfirst( $term );
				wp_insert_term( $term, $tax );
			}
		}
	}
}
