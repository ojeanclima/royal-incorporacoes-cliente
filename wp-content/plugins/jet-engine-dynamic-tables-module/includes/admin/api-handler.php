<?php
namespace Jet_Engine_Dynamic_Tables\Admin;

use Jet_Engine_Dynamic_Tables\Plugin;

class API_Handler {

	private $slug;

	public function __construct( $slug ) {

		$this->slug = $slug;

		add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'init_callbacks' ) );

	}

	/**
	 * Initialize callbacks
	 *
	 * @return [type] [description]
	 */
	public function init_callbacks( $api_manager ) {

		$endpoints = array(
			'get-item',
			'add-item',
			'edit-item',
			'delete-item',
			'get-items',
		);

		foreach ( $endpoints as $endpoint ) {

			$callback = str_replace( '-', '_', $endpoint );

			if ( is_callable( array( $this, $callback ) ) ) {
				add_filter( 'jet-engine/rest-api/' . $endpoint . '/' . $this->slug, array( $this, $callback ), 10, 3 );
			}

		}

		$api_manager->register_endpoint( new Rest_API\Table_Fetch_Data() );

	}

	/**
	 * Get item callback
	 *
	 * @param  [type] $params   [description]
	 * @param  [type] $endpoint [description]
	 * @return [type]           [description]
	 */
	public function get_items( $res, $params, $endpoint ) {

		$items = Plugin::instance()->data->get_items();
		$items = array_map( array( $endpoint, 'prepare_item' ), $items );
		$items = array_map( function ( $item ) {
			$item['meta_fields'] = maybe_unserialize( $item['meta_fields'] );
			return $item;
		}, $items );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $items,
		) );

	}

	/**
	 * Delete item callback
	 *
	 * @param  [type] $params   [description]
	 * @param  [type] $endpoint [description]
	 * @return [type]           [description]
	 */
	public function delete_item( $res, $params, $endpoint ) {

		$id = $params['id'];

		if ( ! $id ) {

			Dashboard::instance()->add_notice(
				'error',
				__( 'Item ID not found in request', 'jet-engine' )
			);

			return rest_ensure_response( array(
				'success' => false,
				'notices' => Dashboard::instance()->get_notices(),
			) );

		}

		Plugin::instance()->data->set_request( array( 'id' => $id ) );

		if ( Plugin::instance()->data->delete_item( false ) ) {
			return rest_ensure_response( array(
				'success' => true,
			) );
		} else {
			return rest_ensure_response( array(
				'success' => false,
				'notices' => Dashboard::instance()->get_notices(),
			) );
		}

	}

	/**
	 * Edit new item callback
	 *
	 * @param  [type] $res      [description]
	 * @param  [type] $params   [description]
	 * @param  [type] $endpoint [description]
	 * @return [type]           [description]
	 */
	public function edit_item( $res, $params, $endpoint ) {

		if ( empty( $params['id'] ) ) {

			Dashboard::instance()->add_notice(
				'error',
				__( 'Item ID not found in request', 'jet-engine' )
			);

			return rest_ensure_response( array(
				'success' => false,
				'notices' => Module::instance()->manager->get_notices(),
			) );

		}

		Plugin::instance()->data->set_request( apply_filters( 'jet-engine/table-builder/edit-table/request', array(
			'id'          => $params['id'],
			'name'        => ! empty( $params['general_settings']['name'] ) ? $params['general_settings']['name'] : '',
			'slug'        => ! empty( $params['general_settings']['slug'] ) ? $params['general_settings']['slug'] : '',
			'args'        => ! empty( $params['general_settings'] ) ? $params['general_settings'] : array(),
			'meta_fields' => ! empty( $params['meta_fields'] ) ? $params['meta_fields'] : array(),
		) ) );

		$updated = Plugin::instance()->data->edit_item( false );

		return rest_ensure_response( array(
			'success' => $updated,
			'notices' => Dashboard::instance()->get_notices(),
		) );

	}

	/**
	 * Add new item callback
	 *
	 * @param  [type] $params   [description]
	 * @param  [type] $endpoint [description]
	 * @return [type]           [description]
	 */
	public function add_item( $res, $params, $endpoint ) {

		Plugin::instance()->data->set_request( apply_filters( 'jet-engine/table-builder/edit-table/request', array(
			'name'        => ! empty( $params['general_settings']['name'] ) ? $params['general_settings']['name'] : '',
			'slug'        => ! empty( $params['general_settings']['slug'] ) ? $params['general_settings']['slug'] : '',
			'args'        => ! empty( $params['general_settings'] ) ? $params['general_settings'] : array(),
			'meta_fields' => ! empty( $params['meta_fields'] ) ? $params['meta_fields'] : array(),
		) ) );

		$item_id = Plugin::instance()->data->create_item( false );

		return rest_ensure_response( array(
			'success' => ! empty( $item_id ),
			'item_id' => $item_id,
			'notices' => Dashboard::instance()->get_notices(),
		) );

	}

	/**
	 * Get item callback
	 *
	 * @param  [type] $params   [description]
	 * @param  [type] $endpoint [description]
	 * @return [type]           [description]
	 */
	public function get_item( $res, $params, $endpoint ) {

		$id = isset( $params['id'] ) ? esc_attr( $params['id'] ) : false;

		if ( ! $id ) {

			Dashboard::instance()->add_notice(
				'error',
				__( 'Item ID not found in request', 'jet-engine' )
			);

			return rest_ensure_response( array(
				'success' => false,
				'notices' => Dashboard::instance()->get_notices(),
			) );

		}

		$item = Plugin::instance()->data->get_item_for_edit( $id );

		if ( ! $item ) {

			Dashboard::instance()->add_notice(
				'error',
				__( 'Post type not found', 'jet-engine' )
			);

			return rest_ensure_response( array(
				'success' => false,
				'notices' => Dashboard::instance()->get_notices(),
			) );

		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $item,
		) );
	}

}
