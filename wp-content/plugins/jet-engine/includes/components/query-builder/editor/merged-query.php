<?php
namespace Jet_Engine\Query_Builder\Query_Editor;

use Jet_Engine\Query_Builder\Manager;

class Merged_Query extends Posts_Query {

	/**
	 * Qery type ID
	 */
	public function get_id() {
		return 'merged-query';
	}

	/**
	 * Qery type name
	 */
	public function get_name() {
		return __( 'Merged Query', 'jet-engine' );
	}

	/**
	 * Returns Vue component name for the Query editor for the current type.
	 * I
	 * @return [type] [description]
	 */
	public function editor_component_name() {
		return 'jet-merged-query';
	}

	/**
	 * Returns Vue component template for the Query editor for the current type.
	 * 
	 * @return [type] [description]
	 */
	public function editor_component_data() {
		
		$allowed_types   = $this->get_allowed_query_types_to_merge();
		$allowed_queries = [];

		foreach( $allowed_types as $type => $type_name ) {
			$allowed_queries[ $type ] = Manager::instance()->get_queries_for_options( true, $type );
		}

		return [
			'allowed_types'   => $allowed_types,
			'allowed_queires' => $allowed_queries,
		];
	}

	/**
	 * Get allowed query types to use as merged query
	 * 
	 * @return [type] [description]
	 */
	public function get_allowed_query_types_to_merge() {
		return apply_filters( 'jet-engine/query-builder/merged-query/allowed-query-types', [
			'posts'    => __( 'Posts', 'jet-engine' ),
			'terms'    => __( 'Terms', 'jet-engine' ),
			'users'    => __( 'Users', 'jet-engine' ),
			'comments' => __( 'Comments', 'jet-engine' ),
		] );
	}

	/**
	 * Returns Vue component template for the Query editor for the current type.
	 * I
	 * @return [type] [description]
	 */
	public function editor_component_template() {
		ob_start();
		include Manager::instance()->component_path( 'templates/admin/types/merged-query.php' );
		return ob_get_clean();
	}

	/**
	 * Returns Vue component template for the Query editor for the current type.
	 * I
	 * @return [type] [description]
	 */
	public function editor_component_file() {
		return Manager::instance()->component_url( 'assets/js/admin/types/merged-query.js' );
	}

}
