<?php
namespace Jet_Engine\Modules\Maps_Listings\Geosearch\Query;

class Users extends Base {

	public $query_type = 'users';

	public function __construct() {

		parent::__construct();
		add_action( 'pre_user_query', array( $this, 'update_users_query' ) );
		add_filter( 'jet-engine/query-builder/query/items', array( $this, 'add_distance_to_users' ), 10, 2 );
		add_filter( 'jet-engine/listings/data/prop-not-found', array( $this, 'get_distance' ), 10, 3 );
	}

	public function add_distance_to_users( $users, $query ) {
		if ( ! apply_filters( 'jet-engine/maps-listings/add-distance-field/users', false, $query ) ) {
			return $users;
		}
		
		if ( $query->query_type !== 'users' || empty( $query->final_query['geo_query'] ) ) {
			return $users;
		}

		$geo_query = $query->final_query['geo_query'];

		$lat_field = $geo_query['lat_field'] ?? false;
		$lng_field = $geo_query['lng_field'] ?? false;

		if ( ! $lat_field || ! $lng_field ) {
			return $users;
		}

		$units = "miles";
		
		if ( !empty( $geo_query['units'] ) ) {
			$units = strtolower( $geo_query['units'] );
		}

		$radius = 3959;
		
		if ( in_array( $units, array( 'km', 'kilometers' ) ) ) {
			$radius = 6371;
		}

		if ( isset( $geo_query['latitude'] ) ) {
			$lat = $geo_query['latitude' ];
		}

		if ( isset( $geo_query['longitude'] ) ) {
			$lng = $geo_query['longitude'];
		}

		if ( ! isset( $lat ) || ! isset( $lng ) ) {
			return $users;
		}

		foreach ( $users as $i => $user ) {
			$t_lat = get_user_meta( $user->ID, $lat_field, true );
			$t_lng = get_user_meta( $user->ID, $lng_field, true );

			if ( \Jet_Engine_Tools::is_empty( $t_lat ) || \Jet_Engine_Tools::is_empty( $t_lng ) ) {
				continue;
			}

			$users[ $i ]->{ $this->distance_term } = $this->haversine_raw( ( float ) $radius, ( float ) $lat, ( float ) $lng, ( float ) $t_lat, ( float ) $t_lng );
		}

		return $users;
	}

	public function update_users_query( $query ) {
		
		if ( empty( $query->query_vars['geo_query'] ) ) {
			return;
		}

		$geo_query = $query->query_vars['geo_query'];

		global $wpdb;

		$query->query_from .= " ";
		$query->query_from .= "INNER JOIN $wpdb->usermeta AS geo_query_lat ON ( $wpdb->users.ID = geo_query_lat.user_id ) ";
		$query->query_from .= "INNER JOIN $wpdb->usermeta AS geo_query_lng ON ( $wpdb->users.ID = geo_query_lng.user_id ) ";

		$lat_field = 'latitude';
		if ( ! empty( $geo_query['lat_field'] ) ) {
			$lat_field =  $geo_query['lat_field'];
		}

		$lng_field = 'longitude';
		if ( !empty( $geo_query['lng_field'] ) ) {
			$lng_field =  $geo_query['lng_field'];
		}

		$distance = 20;
		if ( isset( $geo_query['distance'] ) ) {
			$distance = $geo_query['distance'];
		}

		$haversine = $this->haversine_term( $geo_query );
		$new_sql   = " AND ( geo_query_lat.meta_key = %s AND geo_query_lng.meta_key = %s AND " . $haversine . " <= %f )";
		
		$query->query_where .= $wpdb->prepare( $new_sql, $lat_field, $lng_field, $distance );

		$orderby = $query->get( 'orderby' );

		if ( 'distance' === $orderby ) {
			
			$order = $query->get('order');

			if ( ! $order ) {
				$order = 'ASC';
			}

			$query->query_fields  .= ", $haversine AS $this->distance_term";
			$query->query_orderby = "ORDER BY $this->distance_term $order";

		}

	}

	public function get_distance( $value, $property, $object ) {
		if ( $property !== 'geo_query_distance' || ! isset( $object->$property ) ) {
			return $value;
		}
		
		return $object->$property;
	}

}
