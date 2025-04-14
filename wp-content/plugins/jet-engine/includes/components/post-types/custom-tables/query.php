<?php
namespace Jet_Engine\CPT\Custom_Tables;

/**
 * Query management class
 */
class Query {

	public $db;
	public $object_type;
	public $object_slug;
	public $fields;

	public function __construct( $db = null, $object_type = 'post', $object_slug = '', $fields = [] ) {

		$this->db          = $db;
		$this->object_type = $object_type;
		$this->object_slug = $object_slug;
		$this->fields      = $fields;

		$this->hook_query_handlers();

		// Filters compatibility hooks
		add_filter( 'jet-smart-filters/pre-get-indexed-data', [ $this, 'add_indexer_handler' ], 10, 4 );
		add_filter( 'jet-smart-filters/range-filter/string-callback-callable', [ $this, 'set_range_min_max_callback' ] );
		add_filter( 'jet-smart-filters/range/source-callbacks', [ $this, 'register_range_min_max_callback' ] );
		add_filter( 'jet-smart-filters/indexer/get-post-meta', [ $this, 'index_serialized_fields' ], 10, 2 );

	}

	/**
	 * Generate slug for min/max callback for filters
	 * 
	 * @return [type] [description]
	 */
	public function min_max_callback_slug() {
		return 'jet_engine_custom_storage_' . $this->object_type . '_' . $this->object_slug;
	}

	/**
	 * Add min/max callback for current meta storage
	 * 
	 * @param  [type] $callbacks [description]
	 * @return [type]            [description]
	 */
	public function register_range_min_max_callback( $callbacks = [] ) {
		$label = ucwords( str_replace( [ '_', '-' ], ' ', $this->object_slug ) );
		$callbacks[ $this->min_max_callback_slug() ] = $label . ': ' . __( 'Get from custom storage by query meta key', 'jet-engine' );
		return $callbacks;
	}

	/**
	 * Replace string callback with actual callable object
	 * 
	 * @param [type] $callback [description]
	 */
	public function set_range_min_max_callback( $callback = '' ) {

		if ( $callback === $this->min_max_callback_slug() ) {
			$callback = [ $this, 'apply_min_max_callback' ];
		}

		return $callback;

	}

	/**
	 * Get min/max values for given key
	 * 
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function apply_min_max_callback( $args = [] ) {

		$field_name = ! empty( $args['key'] ) ? $args['key'] : false;

		if ( ! $field_name ) {
			return [];
		}

		$new_data = $this->db->wpdb()->get_results( sprintf(
			'SELECT min( FLOOR( %1$s ) ) as min, max( CEILING( %1$s ) ) as max FROM %2$s',
			esc_attr( $field_name ),
			$this->db->table()
		) );

		$min = ! empty( $new_data ) ? $new_data[0]->min : 0;
		$max = ! empty( $new_data ) ? $new_data[0]->max : 0;

		return [
			'min' => $min,
			'max' => $max,
		];

	}

	/**
	 * Serialized data we indexing in old way
	 * 
	 * @return [type] [description]
	 */
	public function index_serialized_fields( $indexed_data = [], $metadata_to_index = [] ) {

		if ( ! empty( $metadata_to_index['serialized'] ) ) {
			foreach ( $metadata_to_index['serialized'] as $key => $data ) {
				if ( in_array( $key, $this->fields ) ) {
					
					$custom_storage_index = [];

					foreach ( $data as &$value ) {
						$value = addslashes( $value );
					}

					$sql_data  = esc_sql( implode( '|', $data ) );
					$sql_where = "( $key REGEXP '[\'\"]?;s:4:\"true\"|\:[\'\"]?($sql_data)[\'\"]?;[^s]' )";
					$table     = $this->db->table();

					$custom_storage_index = $this->db->wpdb()->get_results( "
						SELECT object_ID as item_id, '$key' as item_key, 'post' as type, $key as item_value
						FROM $table
						WHERE $sql_where
						ORDER BY item_id ASC
					", ARRAY_A );

					if ( ! empty( $custom_storage_index ) ) {
						$indexed_data = array_merge( $indexed_data, $custom_storage_index );
					}

				}
			}
		}

		return $indexed_data;

	}

	/**
	 * Get indexed data for filters by custom storage fields.
	 * In this way we processed all fields except serialized data (checkboxes, multiselects)
	 * 
	 * @param  [type] $data       [description]
	 * @param  [type] $provider   [description]
	 * @param  [type] $query_args [description]
	 * @param  [type] $indexer    [description]
	 * @return [type]             [description]
	 */
	public function add_indexer_handler( $data, $provider, $query_args, $indexer ) {

		if (
			! isset( $query_args['post_type'] )
			|| ! $this->post_type_supported( $query_args['post_type'] )
		) {
			return $data;
		}

		$meta_query = isset( $indexer->indexing_data[ $provider ]['meta_query'] ) ? $indexer->indexing_data[ $provider ]['meta_query'] : [];

		if ( empty( $meta_query ) ) {
			return $data;
		}

		$filtered_keys = array_keys( $meta_query );
		$filtered_keys = array_map( function( $f_key ) {
			$f_key = explode( '|', $f_key );
			return $f_key[0];
		}, $filtered_keys );

		$has_custom_storage_fields = array_intersect( $filtered_keys, $this->fields );

		if ( ! empty( $has_custom_storage_fields ) ) {
			add_filter(
				'jet-smart-filters/filters/indexed-data/query-type-data',
				[ $this, 'adjust_indexed_data' ], 
				10, 7 
			);
		}

		return $data;
	}

	/**
	 * Add counters from custom storage to indexed data
	 * 
	 * @return [type] [description]
	 */
	public function adjust_indexed_data( $indexed_data, $query_type, $key = '', $data = [], $indexer = null, $queried_ids = [], $provider_key = '' ) {

		if ( 'meta_query' !== $query_type ) {
			return $indexed_data;
		}

		$key_data = explode( '|', $key );
		$key      = $key_data[0];
		$suffix   = isset( $key_data[1] ) ? $key_data[1] : false;

		if ( ! in_array( $key, $this->fields ) ) {
			return $indexed_data;
		}

		$new_data = $this->db->wpdb()->get_results( sprintf(
			'SELECT %1$s, COUNT(%1$s) AS count FROM %2$s WHERE object_ID IN ( %3$s ) GROUP BY %1$s',
			esc_attr( $key ),
			$this->db->table(),
			implode(',', $queried_ids )
		) );

		$i = 0;

		if ( ! empty( $new_data ) ) {

			$suffix_str = ( ! empty( $suffix ) ) ? '|' . $suffix : '';

			if (
				empty( $indexed_data[ $key ] )
				&& isset( $indexer->indexing_data[ $provider_key ]['meta_query'][ $key . $suffix_str ] )
			) {
				$indexed_data[ $key ] = array_fill_keys( 
					array_keys( $indexer->indexing_data[ $provider_key ]['meta_query'][ $key . $suffix_str ] ), 
					0
				);
			}

			foreach( $new_data AS $item ) {

				switch ( $suffix ) {
					
					case 'range':

						foreach ( $indexed_data[ $key ] as $range_interval => $value ) {

							if ( 0 === $i ) {
								$value = 0;
							}

							$interval_data = explode( '_', $range_interval );
							$min = absint( $interval_data[0] );
							$max = absint( $interval_data[1] );
							$current_key = absint( $item->$key );

							if ( $min <= $current_key && $current_key <= $max ) {
								$value += absint( $item->count );
							}

							$indexed_data[ $key ][ $range_interval ] = $value;

						}
						break;
					
					default:
						$indexed_data[ $key ][ $item->$key ] = $item->count;
						break;
				}

				$i++;

			}
		}

		return $indexed_data;

	}

	/**
	 * Check if is query of custom table type
	 * 
	 * @param  [type]  $query       [description]
	 * @param  [type]  $object_type [description]
	 * @return boolean              [description]
	 */
	public function is_query_of_type( $query, $object_type ) {

		$object_type = $this->object_type;
		$result      = false;

		switch ( $object_type ) {

			case 'post':
		
				$post_type  = $this->get_query_prop( $query, 'post_type' );
				$meta_query = $this->get_query_prop( $query, 'meta_query' );
				$orderby    = $this->get_query_prop( $query, 'orderby' );

				if ( $this->post_type_supported( $post_type ) 
					&& ( ! empty( $meta_query ) || ! empty( $orderby ) ) 
				) {
					$result = true;
				}

				break;

			default:

				$result = apply_filters( 'jet-engine/custom-meta-tables/query/is-query-of-type', false, $query, $this );
				break;

		}

		return $result;

	}

	/**
	 * Check if post type is supports custom meta storage
	 * 
	 * @param  string $post_type [description]
	 * @return [type]            [description]
	 */
	public function post_type_supported( $post_type = '' ) {

		if ( ! $post_type ) {
			return false;
		}

		if ( ! is_array( $post_type ) ) {
			$post_type = [ $post_type ];
		}

		return ( in_array( $this->object_slug, $post_type ) ) ? true : false;
	}

	/**
	 * Returns property from query with checking type of query - object or array
	 * 
	 * @param  [type] $query [description]
	 * @param  [type] $prop  [description]
	 * @return [type]        [description]
	 */
	public function get_query_prop( $query, $prop ) {

		if ( is_object( $query ) ) {
			return $query->get( $prop );
		}

		if ( is_array( $query ) ) {
			return isset( $query[ $prop ] ) ? $query[ $prop ] : false;
		}

		return false;
	}

	/**
	 * Hook query handlers
	 * 
	 * @return [type] [description]
	 */
	public function hook_query_handlers() {

		$object_type = $this->object_type;

		switch ( $object_type ) {

			case 'post':

				add_action( 'pre_get_posts', function( $query ) use ( $object_type ) {

					if ( ! $this->is_query_of_type( $query, $object_type ) ) {
						return false;
					}

					$meta_query    = $query->get( 'meta_query' );
					$meta_partials = $this->exctract_meta_query_partials( $meta_query );
					$custom_order  = [];

					$query_order_by = $query->get( 'orderby' );
					$query_order    = $query->get( 'order' );

					if ( $query_order_by ) {

						if ( ! is_array( $query_order_by ) ) {
							$query_order_by = [ $query_order_by => $query_order ];
						}

						$unset_orders = [];

						foreach ( $query_order_by as $order_by => $order ) {
							if ( in_array( $order_by, [ 'meta_value_num', 'meta_value' ] ) ) {
								
								$meta_key = $query->get( 'meta_key' );
								$order    = ! empty( $order ) ? $order : 'DESC';
								$suffix   = ( 'meta_value_num' === $order_by ) ? '+0' : '';

								if ( in_array( $meta_key, $this->fields ) ) {
									$unset_orders[] = $order_by;
									$query->set( 'meta_key', null );
									$custom_order[ $meta_key . $suffix ] = $order;
								}
							}

							if ( ! empty( $meta_partials['custom_query'] ) 
								&& isset( $meta_partials['custom_query'][ $order_by ] ) 
							) {
								$clause = $meta_partials['custom_query'][ $order_by ];
								$meta_key = $clause['key'];
								$unset_orders[] = $order_by;
								$order = ! empty( $order ) ? $order : 'DESC';
								$type = $clause['type'] ?? '';
								$numeric_types = [ 'TIMESTAMP', 'NUMERIC', 'DECIMAL', 'SIGNED' ];
								$suffix = in_array( $type, $numeric_types ) ? '+0' : '';
								$custom_order[ $meta_key . $suffix ] = $order;
							}

						}

						if ( ! empty( $unset_orders ) ) {

							foreach ( $unset_orders as $order ) {
								unset( $query_order_by[ $order ] );
							}

							$query->set( 'orderby', $query_order_by );

						}

					}

					if ( ! empty( $meta_partials['custom_query'] ) || ! empty( $custom_order ) ) {

						$custom_query = $meta_partials['custom_query'] ?? [];
						
						$query->set( 'custom_table_query', [
							'table' => $this->db->table(),
							'query' => $custom_query,
							'order' => $custom_order,
						] );

					}
					
					$query->set( 'meta_query', $meta_partials['meta_query'] );

				} );

				break;

			default:

				do_action( 'jet-engine/custom-meta-tables/query/handle-object-type-query', $this );
				break;

		}
	}

	/**
	 * Extract meta query parts - custom query and meta query
	 * 
	 * @param  array  $meta_query [description]
	 * @return [type]             [description]
	 */
	public function exctract_meta_query_partials( $meta_query = [] ) {

		$result = [
			'custom_query' => false,
			'meta_query'   => false,
		];

		$custom_query     = [];
		$plain_meta_query = [];
		$relation         = false;

		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {

			foreach ( $meta_query as $meta_clause_key => $meta_clause ) {

				if ( 'relation' === $meta_clause_key ) {
					$relation = $meta_clause;
				}

				if ( is_array( $meta_clause ) && isset( $meta_clause['key'] ) ) {

					if ( in_array( $meta_clause['key'], $this->fields ) ) {
						$custom_query[ $meta_clause_key ] = $meta_clause;
					} else {
						$plain_meta_query[ $meta_clause_key ] = $meta_clause;
					}

				} elseif ( is_array( $meta_clause ) && ! isset( $meta_clause['key'] ) ) {

					$partials = $this->exctract_meta_query_partials( $meta_clause );

					if ( ! empty( $partials['meta_query'] ) ) {
						$plain_meta_query[ $meta_clause_key ] = $partials['meta_query'];
					}

					if ( ! empty( $partials['custom_query'] ) ) {
						$custom_query[ $meta_clause_key ] = $partials['custom_query'];
					}
				}

				if ( ! empty( $custom_query ) ) {

					if ( $relation ) {
						$custom_query['relation'] = $relation;
					}

					$result['custom_query'] = $custom_query;
				}

				if ( ! empty( $plain_meta_query ) ) {

					if ( $relation ) {
						$plain_meta_query['relation'] = $relation;
					}

					$result['meta_query'] = $plain_meta_query;
				}

			}

		}

		return $result;

	}

}
