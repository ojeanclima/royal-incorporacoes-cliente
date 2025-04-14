<?php
namespace Jet_Engine_Dynamic_Tables\Admin\Rest_API;

use Jet_Engine\Query_Builder\Manager as Query_Builder;

class Table_Fetch_Data extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'table-fetch-data';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params = $request->get_params();
		$query_id = ! empty( $params['query_id'] ) ? $params['query_id'] : false;

		if ( ! $query_id ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Query ID not found in the request', 'jet-engine' ),
			) );
		}

		$query = Query_Builder::instance()->get_query_by_id( $query_id );

		if ( ! $query ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Can`t find the query object', 'jet-engine' ),
			) );
		}

		$items = $query->get_items();

		if ( empty( $items ) ) {
			rest_ensure_response( array(
				'success' => true,
				'message' => __( 'Query returns an empty result', 'jet-engine' ),
			) );
		}

		$items   = array_values( $items );
		$item    = $items[0];
		$columns = array();

		if ( is_array( $item ) ) {
			$columns = array_keys( $item );
		} elseif ( is_object( $item ) ) {
			$columns = array_keys( get_object_vars( $item ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'columns' => $columns,
			'data'    => $items[0],
		) );

	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

}
