<?php
namespace Jet_Engine\CPT\Custom_Tables;
/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Base DB class
 */
class DB extends \Jet_Engine_Base_DB {

	/**
	 * Returns table columns schema
	 *
	 * @return [type] [description]
	 */
	public function get_table_schema() {

		$charset_collate = $this->wpdb()->get_charset_collate();
		$table           = $this->table();
		$columns_schema  = '';

		foreach ( $this->schema as $column => $definition ) {

			if ( isset( $default_columns[ $column ] ) ) {
				continue;
			}

			if ( ! $definition ) {
				$definition = 'text';
			}

			$columns_schema .= $column . ' ' . $definition . ',';

		}

		return "CREATE TABLE $table (
			$columns_schema
			PRIMARY KEY ( meta_ID ),
			KEY object_ID ( object_ID )
		) $charset_collate;";
	}

	/**
	 * Insert booking
	 *
	 * @param  array  $booking [description]
	 * @return [type]          [description]
	 */
	public function insert( $data = [] ) {

		if ( ! empty( $this->defaults ) ) {
			foreach ( $this->defaults as $default_key => $default_value ) {
				if ( ! isset( $data[ $default_key ] ) ) {
					$data[ $default_key ] = $default_value;
				}
			}
		}

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$value        = maybe_serialize( $value );
				$data[ $key ] = $value;
			}
		}

		$inserted = $this->wpdb()->insert( $this->table(), $data );

		if ( $inserted ) {
			return self::wpdb()->insert_id;
		} else {
			return false;
		}
	}

	/**
	 * Update appointment info
	 *
	 * @param  array  $new_data [description]
	 * @param  array  $where    [description]
	 * @return [type]           [description]
	 */
	public function update( $new_data = array(), $where = array() ) {

		if ( ! empty( $this->defaults ) ) {
			foreach ( $this->defaults as $default_key => $default_value ) {
				if ( ! isset( $data[ $default_key ] ) ) {
					$data[ $default_key ] = $default_value;
				}
			}
		}

		foreach ( $new_data as $key => $value ) {
			if ( is_array( $value ) ) {
				$value            = maybe_serialize( $value );
				$new_data[ $key ] = $value;
			}
		}

		self::wpdb()->update( $this->table(), $new_data, $where );

		/**
		 * https://github.com/Crocoblock/suggestions/issues/7774
		 */
		$this->reset_found_items_cache();
	}

	/**
	 * Query data from db table
	 *
	 * @return [type] [description]
	 */
	public function query( $args = array(), $limit = 0, $offset = 0, $order = array(), $rel = 'AND' ) {

		$table = $this->table();
		$query = array();
		
		$query['select'] = "SELECT * FROM $table";

		if ( ! $rel ) {
			$rel = 'AND';
		}

		$where = $this->add_where_args( $args, $rel );
		
		$query['where'] = ! empty( $where ) ? $where : " WHERE 1=1";

		if ( empty( $order ) ) {
			$order = array( array(
				'orderby' => 'meta_ID',
				'order'   => 'desc',
			) );
		}

		$query['order'] = $this->add_order_args( $order );

		if ( intval( $limit ) > 0 ) {
			$limit          = absint( $limit );
			$offset         = absint( $offset );
			$query['limit'] = " LIMIT $offset, $limit";
		}

		$query = apply_filters( 'jet-engine/custom-meta-tables/db/sql-query-parts', $query, $table, $args, $this );
		$query = implode( '', $query );

		$raw = self::wpdb()->get_results( $query, $this->get_format_flag() );

		return $raw;

	}

}
