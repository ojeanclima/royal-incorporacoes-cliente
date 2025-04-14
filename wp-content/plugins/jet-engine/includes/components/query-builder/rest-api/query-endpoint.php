<?php
namespace Jet_Engine\Query_Builder\Rest;

use Jet_Engine\Query_Builder\Manager;

class Query_Endpoint {

	private $settings = [];
	private $query_instance = null;

	public function __construct( $settings = [] ) {
		$this->settings = $settings;
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	/**
	 * Return settings required to register endpoint
	 * @param  string $setting [description]
	 * @return [type]          [description]
	 */
	public function get_settings( $setting = '', $default = false ) {
		
		if ( ! $setting ) {
			return $this->settings;
		}

		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;

	}

	public function register_route() {

		register_rest_route( 
			sanitize_text_field( $this->get_settings( 'api_namespace' ) ),
			sanitize_text_field( $this->get_settings( 'api_path' ) ),
			[
				'methods' => 'GET',
				'callback' => [ $this, 'callback' ],
				'permission_callback' => [ $this, 'permission_callback' ],
				'args' => $this->get_args(),
			]
		);

	}

	/**
	 * Returns query arguments
	 * 
	 * @return [type] [description]
	 */
	public function get_args() {

		$schema = $this->get_settings( 'api_schema' );

		if ( empty( $schema ) ) {
			return [];
		}

		$result = [];

		foreach ( $schema as $item ) {
			if ( ! empty( $item['arg'] ) ) {
				$result[ $item['arg'] ] = [
					'type'    => 'string',
					'default' => $item['value'],
				];
			}
		}

		return $result;

	}

	/**
	 * Check if user from request has access to this endpoint
	 * 
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function permission_callback( $request ) {

		$api_access = $this->get_settings( 'api_access', 'public' );
		$result     = false;

		if ( 'public' === $api_access ) {
			$result = true;
		}

		if ( 'role' === $api_access ) {
			$result = $this->check_access_by_role();
		}

		if ( 'cap' === $api_access ) {
			$result = $this->check_access_by_capability();
		}

		return apply_filters( 'jet-engine/query-builder/query-rest-api/has-access', $result, $request, $this );
	}

	/**
	 * Check if current user has access by user role
	 * 
	 * @return [type] [description]
	 */
	public function check_access_by_role() {

		$roles = $this->get_settings( 'api_access_role' );

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$roles = array_filter( $roles );

		// If no roles set - allow for any registered user
		if ( empty( $roles ) ) {
			return true;
		}

		$user = wp_get_current_user();

		foreach ( $roles as $role ) {
			if ( in_array( $role, (array) $user->roles ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Check if current user has access by user capability
	 * 
	 * @return [type] [description]
	 */
	public function check_access_by_capability() {

		$capabilities = $this->get_settings( 'api_access_cap' );
		$capabilities = explode( ',', $capabilities );

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$capabilities = array_filter( $capabilities );

		// If no roles set - allow for any registered user
		if ( empty( $capabilities ) ) {
			return true;
		}

		foreach ( $capabilities as $cap ) {
			if ( current_user_can( trim( $cap ) ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Returns query result
	 * 
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$query_id = $this->get_settings( 'id' );

		if ( ! $query_id ) {
			return new \WP_Error( 'query_id_missing', __( 'Query ID is missing. Can`t complete the request', 'jet-engine' ) );
		}

		$query = Manager::instance()->get_query_by_id( $query_id );

		if ( ! $query ) {
			return new \WP_Error( 'query_missing', __( 'Query with given ID not found. Can`t complete the request', 'jet-engine' ) );
		}

		$args = $this->get_args();

		if ( ! empty( $args ) ) {
			foreach ( $args as $arg => $data ) {
				if ( ! isset( $_REQUEST[ $arg ] ) ) {
					$_REQUEST[ $arg ] = $data['default'];
				}
			}
		}

		return new \WP_REST_Response( $query->get_items(), 200 );

	}

}
