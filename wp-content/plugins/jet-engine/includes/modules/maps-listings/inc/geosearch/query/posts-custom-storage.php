<?php
namespace Jet_Engine\Modules\Maps_Listings\Geosearch\Query;

class Posts_Custom_Storage extends Posts {

	/**
	 * Public function get geoquery from give query
	 *
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function get_geo_query( $query ) {

		return apply_filters(
			'jet-engine/maps-listings/geosearch/posts-custom-storage/get-geo-query',
			false,
			$query
		);
	}

	public function add_distance_field( $fields ) {
		return $fields;
	}

	public function lat_field( $geo_query, $format = 'field' ) {

		$lat_field = 'latitude';
		if ( ! empty( $geo_query['lat_field'] ) ) {
			$lat_field =  $geo_query['lat_field'];
		}

		switch ( $format ) {
			case 'field':
				return 'geo_query.' . $lat_field;

			case 'query':
				return $lat_field;
		}
	}

	public function lng_field( $geo_query, $format = 'field' ) {

		$lng_field = 'longitude';
		if ( ! empty( $geo_query['lng_field'] ) ) {
			$lng_field =  $geo_query['lng_field'];
		}

		switch ( $format ) {
			case 'field':
				return 'geo_query.' . $lng_field;

			case 'query':
				return $lng_field;
		}
	}

	public function posts_join( $sql, $query ) {

		global $wpdb;

		$geo_query = $this->get_geo_query( $query );

		if ( $geo_query && ! empty( $geo_query['geo_query_table'] ) ) {

			if ( $sql ) {
				$sql .= ' ';
			}

			$table = $geo_query['geo_query_table'];

			$sql .= "INNER JOIN $table AS geo_query ON ( $wpdb->posts.ID = geo_query.object_ID ) ";
		}

		return $sql;
	}

	// match on the right metafields, and filter by distance
	public function posts_where( $sql, $query ) {

		global $wpdb;

		$geo_query = $this->get_geo_query( $query );

		if ( $geo_query ) {

			$distance = 20;
			if ( isset( $geo_query['distance'] ) ) {
				$distance = $geo_query['distance'];
			}

			if ( $sql ) {
				$sql .= " AND ";
			}

			$haversine = $this->haversine_term( $geo_query );
			$new_sql = "( $haversine <= %f )";
			$sql .= $wpdb->prepare( $new_sql, $distance );
		}

		return $sql;

	}

	// handle ordering
	public function posts_orderby( $sql, $query ) {

		$geo_query = $this->get_geo_query( $query );

		if ( $geo_query ) {

			$orderby = $query->get('orderby');
			$order   = $query->get('order');

			if ( $orderby == 'distance' || ( is_array( $orderby ) && isset( $orderby['distance'] ) ) ) {

				if ( ! $order ) {
					$order = 'ASC';
				}

				$order = ( is_array( $orderby ) && ! empty( $orderby['distance'] ) ) ? $orderby['distance'] : $order;

				$distance_orderby = $this->distance_term . ' ' . $order;

				if ( is_array( $orderby ) && 1 < count( $orderby ) ) {
					$sql_array      = ! empty( $sql ) ? explode( ', ', $sql ) : array();
					$distance_index = array_search( 'distance', array_keys( $orderby ) );

					if ( 0 == $distance_index ) {
						array_unshift( $sql_array, $distance_orderby );
					} else {
						$sql_array = array_merge(
							array_slice( $sql_array, 0, $distance_index ),
							array( $distance_orderby ),
							array_slice( $sql_array, $distance_index, null )
						);
					}

					$sql = implode( ', ', $sql_array );

				} else {
					$sql = $distance_orderby;
				}
			}
		}

		return $sql;

	}

}
