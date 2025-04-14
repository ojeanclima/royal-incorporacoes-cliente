<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Relations extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::RELATIONS_ID;
	}

	/**
	 * Return list of slugs reserved to create Users relation for
	 *
	 * @return array
	 */
	public function reserved_user_slugs() {
		return [ 'user', 'users' ];
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		if ( empty( $data['relations'] ) || empty( $data['objects'] ) ) {
			return $result;
		}

		$allowed_types = [
			'one_to_many',
			'one_to_one',
			'many_to_many',
		];

		$sources_map = [
			'post_type' => 'posts',
			'taxonomy'  => 'terms',
			'cct'       => 'cct',
			'mix'       => 'mix',
		];

		foreach ( $data['relations'] as $raw_relation ) {

			if ( empty( $raw_relation['type'] ) || ! in_array( $raw_relation['type'], $allowed_types ) ) {
				continue;
			}

			$parent = $raw_relation['from'];
			$child  = $raw_relation['to'];
			$slug   = $parent . '>' . $child;

			if ( in_array( $parent, $this->reserved_user_slugs() ) ) {
				$parent = 'users';
				$parent_data = [
					'title' => 'Users',
					'type'  => 'mix',
					'slug'  => $parent,
				];
			} else {
				$parent_data = isset( $data['objects'][ $parent ] ) ? $data['objects'][ $parent ] : false;
			}

			if ( in_array( $child, $this->reserved_user_slugs() ) ) {
				$child = 'users';
				$child_data = [
					'title' => 'Users',
					'type'  => 'mix',
					'slug'  => $child,
				];
			} else {
				$child_data  = isset( $data['objects'][ $child ] ) ? $data['objects'][ $child ] : false;
			}

			if ( ! $parent_data || ! $child_data ) {
				continue;
			}

			$parent_data['slug'] = $parent;
			$child_data['slug']  = $child;

			$parent = jet_engine()->relations->types_helper->type_name_by_parts(
				$sources_map[ $parent_data['type'] ],
				$parent
			);

			$child = jet_engine()->relations->types_helper->type_name_by_parts(
				$sources_map[ $child_data['type'] ],
				$child
			);

			$name = sprintf( '%1$s to %2$s', $parent_data['title'], $child_data['title'] );

			jet_engine()->relations->data->set_request( [
				'name'           => $name,
				'parent_object'  => $parent,
				'child_object'   => $child,
				'parent_rel'     => null,
				'type'           => $raw_relation['type'],
				'db_table'       => true,
				'parent_control' => true,
				'child_control'  => true,
			] );

			if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {
				$rel_id = 1;
			} else {
				$rel_id = jet_engine()->relations->data->create_item( false );
			}

			if ( $rel_id ) {

				$this->log_entity( [
					'id'       => $rel_id,
					'slug'     => $slug, // required to get created relation for filter
					'name'     => $name,
					'parent'   => $parent_data,
					'children' => $child_data,
				] );
			}
		}

		return $result;
	}
}
