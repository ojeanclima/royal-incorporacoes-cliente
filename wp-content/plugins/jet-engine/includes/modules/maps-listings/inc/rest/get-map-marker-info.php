<?php
namespace Jet_Engine\Modules\Maps_Listings;

/**
 * Get_Map_Marker_Info endpoint
 */
class Get_Map_Marker_Info extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'get-map-marker-info';
	}

	/**
	 * API callback
	 *
	 * @return void|\WP_Error|\WP_REST_Response
	 */
	public function callback( $request ) {

		$params     = $request->get_params();
		$listing_id = $params['listing_id'];
		$post_id    = $params['post_id'];

		if ( ! $listing_id || ! $post_id ) {
			return rest_ensure_response( array(
				'success' => false,
				'html'    => __( 'Required parameters is not found in request', 'jet-engine' ),
			) );
		}

		if ( false !== strpos( $post_id, '-' ) ) {
			$this->maybe_apply_jet_smart_filters( $params['jsf'] ?? '' );
		}

		$queried_id = 0;

		// Set the current queried object.
		if ( ! empty( $params['queried_id'] ) ) {
			$queried_obj_data = explode( '|', $params['queried_id'] );
			$queried_id       = ! empty( $queried_obj_data[0] ) ? absint( $queried_obj_data[0] ) : false;
			$queried_class    = ! empty( $queried_obj_data[1] ) ? $queried_obj_data[1] : 'WP_Post';

			if ( $queried_id ) {
				jet_engine()->listings->data->set_current_object_by_id( $queried_id, $queried_class );
			}
		}

		// Set the current element id.
		$element_id = $params['element_id'] ?? '';

		jet_engine()->listings->data->set_listing_by_id( $listing_id );

		$post_obj = false;

		$listing_source  = ( ! empty( $params['source'] ) && 'null' !== $params['source'] ) ? $params['source'] : 'posts';
		$source_instance = Module::instance()->sources->get_source( $listing_source );

		if ( $source_instance ) {
			$post_obj = $source_instance->get_obj_by_id( $post_id );
		}

		// For backward compatibility.
		$post_obj = apply_filters(
			'jet-engine/maps-listing/rest/object/' . $listing_source,
			$post_obj,
			$post_id
		);

		if ( ! $post_obj || is_wp_error( $post_obj ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'html'    => __( 'Requested post not found', 'jet-engine' ),
			) );
		}

		$additional_attrs = array();

		if ( isset( $params['geo_query_distance'] ) && $params['geo_query_distance'] >= 0 ) {
			$post_obj->geo_query_distance = $params['geo_query_distance'];
		}

		jet_engine()->frontend->set_listing( $listing_id );

		do_action( 'jet-engine/maps-listings/get-map-marker', $listing_id, $queried_id, $element_id );

		ob_start();

		$content = jet_engine()->frontend->get_listing_item( $post_obj );

		$additional_attrs = apply_filters( 'jet-engine/maps-listings/map-popup-additional-attrs', $additional_attrs, $params, $post_obj );

		$content = sprintf(
			'<div class="jet-map-popup-%1$s jet-listing-dynamic-post-%1$s" data-item-object="%1$s" data-additional-map-popup-data="%3$s">%2$s</div>',
			$post_id,
			$content,
			! empty( $additional_attrs ) ? htmlspecialchars( json_encode( $additional_attrs ) ) : '{}'
		);
		$content = apply_filters( 'jet-engine/maps-listings/marker-content', $content, $post_obj, $listing_id );

		$content .= ob_get_clean();

		$result = array(
			'success' => true,
			'html'    => $content,
		);

		return rest_ensure_response( $result );

	}

	public function maybe_apply_jet_smart_filters( string $url ) {
		if ( empty( $url ) ) {
			return;
		}

		if ( ! function_exists( 'jet_smart_filters' ) ) {
			return;
		}

		$server_uri = $_SERVER['REQUEST_URI'];

		global $wp;

		$_SERVER['REQUEST_URI'] = preg_replace(
			site_url(),
			'',
			$url
		);

		$wp->parse_request();
		$wp->query_posts();
		wp_reset_postdata();

		jet_smart_filters()->render->apply_filters_from_permalink( $wp );

		$_SERVER['REQUEST_URI'] = $server_uri;
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

	/**
	 * Check user access to current end-popint
	 * This is public endpoint so it always accessible
	 * 
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return true;
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return array(
			'listing_id' => array(
				'default'  => 0,
				'required' => true,
			),
			'post_id' => array(
				'default'  => 0,
				'required' => true,
			),
			'source' => array(
				'default'  => 'posts',
				'required' => false,
			),
			'queried_id' => array(
				'default'  => '',
				'required' => false,
			),
			'geo_query_distance' => array(
				'default'  => -1,
				'required' => false,
			),
		);
	}

}
