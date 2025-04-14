<?php
namespace Jet_Engine\Relations\Storage;

/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define ordering handler class
 */
class Ordering {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Returns ordering mode.
	 * Mode can be adjusted (or totally disabled) for client project requirements
	 *
	 * @return mixed
	 */
	public function get_mode() {
		/**
		 * Allowed modes:
		 * - string 'date' - default. On reorder items dates will be updated to match a new order. Getting related data will be updated to be sorted by date.
		 * - string 'id' - On reorder old items will be removed, new one will be created with a new order.
		 * - bool false - disable an ability to reorder items.
		 */
		return apply_filters( 'jet-engine/relations/ordering-mode', 'date' );
	}

	/**
	 * Process items reorder
	 *
	 * @param  Relation $relation relation instance
	 * @param  array    $items    updated items order
	 * @return void
	 */
	public function reorder_relation_items( $relation, $items ) {

		switch ( $this->get_mode() ) {
			case 'id':
				$this->process_id_reorder( $relation, $items );
				break;

			case 'date':
				$this->process_date_reorder( $relation, $items );
				break;
		}
	}

	/**
	 * Process reorder based on new IDs
	 *
	 * @param  Relation $relation relation instance
	 * @param  array    $items    updated items order
	 * @return void
	 */
	public function process_id_reorder( $relation, $items = [] ) {

		$to_delete = [];
		$to_add    = [];

		foreach( $items as $item ) {
			$to_delete[] = absint( $item['_ID'] );
			$to_add[]    = sprintf( '( %s )', implode( ', ', [
				absint( $item['rel_id'] ),
				absint( $item['parent_rel'] ),
				absint( $item['parent_object_id'] ),
				absint( $item['child_object_id'] ),
			] ) );
		}

		$delete_ids   = implode( ', ', $to_delete );
		$table        = $relation->db->table();
		$insert_items = implode( ', ', array_reverse( $to_add ) );

		$relation->db::wpdb()->query( "DELETE FROM {$table} WHERE _ID IN ({$delete_ids})" );
		$relation->db::wpdb()->query(
			"INSERT INTO {$table} (rel_id, parent_rel, parent_object_id, child_object_id)
			VALUES $insert_items;"
		);
	}

	/**
	 * Process reorder based on new dates
	 *
	 * @param  Relation $relation relation instance
	 * @param  array    $items    updated items order
	 * @return void
	 */
	public function process_date_reorder( $relation, $items = [] ) {

		$base_date = wp_date( 'Y-m-d' );

		$case = [];
		$ids  = [];

		foreach( array_reverse( $items ) as $index => $item ) {
			$new_time = gmdate( 'H:i:s', $index + 1 );
			$case[] = sprintf( 'WHEN _ID = %1$d THEN \'%2$s %3$s\'', $item['_ID'], $base_date, $new_time );
			$ids[]  = $item['_ID'];
		}

		$to_update = implode( "\n", $case );
		$where_ids = implode( ',', $ids );
		$table     = $relation->db->table();

		$relation->db::wpdb()->query(
			"UPDATE $table
			SET created = CASE
			$to_update
			ELSE created
			END
			WHERE _ID IN ($where_ids);"
		);
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Engine
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
