<?php
namespace Jet_Engine\Modules\Maps_Listings\Geosearch\Query;

class Terms extends Base {

	public $query_type = 'terms';

	public function __construct() {

		parent::__construct();
		add_action( 'terms_clauses', array( $this, 'update_terms_clauses' ), 10, 3 );
		add_filter( 'jet-engine/query-builder/query/items', array( $this, 'add_distance_to_terms' ), 10, 2 );

	}

	public function add_distance_to_terms( $terms, $query ) {
		if ( ! apply_filters( 'jet-engine/maps-listings/add-distance-field/terms', false, $query ) ) {
			return $terms;
		}
		
		if ( $query->query_type !== 'terms' || empty( $query->final_query['geo_query'] ) ) {
			return $terms;
		}

		$geo_query = $query->final_query['geo_query'];

		$lat_field = $geo_query['lat_field'] ?? false;
		$lng_field = $geo_query['lng_field'] ?? false;

		if ( ! $lat_field || ! $lng_field ) {
			return $terms;
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
			return $terms;
		}

		foreach ( $terms as $i => $term ) {
			$t_lat = get_term_meta( $term->term_id, $lat_field, true );
			$t_lng = get_term_meta( $term->term_id, $lng_field, true );

			if ( \Jet_Engine_Tools::is_empty( $t_lat ) || \Jet_Engine_Tools::is_empty( $t_lng ) ) {
				continue;
			}

			$terms[ $i ]->{ $this->distance_term } = $this->haversine_raw( ( float ) $radius, ( float ) $lat, ( float ) $lng, ( float ) $t_lat, ( float ) $t_lng );
		}

		return $terms;
	}

	public function update_terms_clauses( $clauses, $taxonomies, $args ) {

		if ( empty( $args['geo_query'] ) ) {
			return $clauses;
		}

		$geo_query = $args['geo_query'];

		global $wpdb;

		$clauses['join'] .= " ";
		$clauses['join'] .= "INNER JOIN $wpdb->termmeta AS geo_query_lat ON ( t.term_id = geo_query_lat.term_id ) ";
		$clauses['join'] .= "INNER JOIN $wpdb->termmeta AS geo_query_lng ON ( t.term_id = geo_query_lng.term_id ) ";

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
		
		$clauses['where'] .= $wpdb->prepare( $new_sql, $lat_field, $lng_field, $distance );

		$orderby = ! empty( $args['orderby'] ) ? $args['orderby'] : 'name';

		if ( 'distance' === $orderby ) {
			
			$clauses['fields'] .= ", $haversine AS $this->distance_term";
			$clauses['orderby'] = "ORDER BY $this->distance_term";

		}

		return $clauses;

	}

}
