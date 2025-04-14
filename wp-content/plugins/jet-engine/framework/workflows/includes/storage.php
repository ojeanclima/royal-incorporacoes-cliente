<?php
/**
 * Workflows UI
 */
namespace Croblock\Workflows;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Storage {

	public $workflows = false;

	protected $table_exists = null;

	public static $registered_namespaces = [];
	public static $user_worksflows = [];

	public function __construct( $workflows ) {
		$this->workflows = $workflows;
	}

	/**
	 * Register new namesapce for current installation
	 * 
	 * @param [type] $namespace [description]
	 * @param [type] $label     [description]
	 */
	public function add_namespace( $namespace, $label ) {
		self::$registered_namespaces[ $this->workflows->prefix() . '_' . $namespace ] = $label;
	}

	/**
	 * Resgiter additinal workflows given by current instance
	 * 
	 * @param  array  $items [description]
	 * @return [type]        [description]
	 */
	public function register_workflows( $items = [] ) {
		self::$user_worksflows = array_merge( self::$user_worksflows, $items );
	}

	/**
	 * Returns all registered namespaces
	 * 
	 * @return [type] [description]
	 */
	public function get_namespaces() {
		return self::$registered_namespaces;
	}

	/** 
	 * Returns an instance of WPDB class
	 * 
	 * @return [type] [description]
	 */
	public function wpdb() {
		global $wpdb;
		return $wpdb;
	}

	/**
	 * Return key of the current storage
	 * 
	 * @return [type] [description]
	 */
	public function get_storage_key() {
		return $this->workflows->prefix() . '_workflows';
	}

	/**
	 * Returns actual workflows list
	 * 
	 * @param  string $namespace [description]
	 * @return [type]            [description]
	 */
	public function get_workflows( $namespace = '' ) {

		$this->ensure_db_table();

		$cache_valid = get_transient( $this->get_storage_key() );
		$items       = false;

		if ( ! $cache_valid ) {
		
			$items = $this->workflows->remote_api()->get_items();

			if ( ! empty( $items ) ) {
				$this->clear_local_storage();
				$this->set_local_storage( $items );
				set_transient( $this->get_storage_key(), true, 3 * DAY_IN_SECONDS );
			}

		}

		if ( empty( $items ) ) {
			$items = $this->get_local_storage();
		}

		$items = ! empty( $items ) ? $items : [];
		$items = array_merge( $items, self::$user_worksflows );

		if ( ! $namespace ) {
			return $items;
		}

		return array_filter( $items, function( $workflow ) use ( $namespace ) {
			return $this->workflows->prefix() . '_' . $workflow['namespace'] === $namespace;
		} );

	}

	public function table() {
		return $this->wpdb()->prefix . $this->get_storage_key();
	}

	/**
	 * Ensure local table for workflows storage exists
	 * 
	 * @return [type] [description]
	 */
	public function ensure_db_table() {

		$table = $this->table();

		if ( null === $this->table_exists ) {
			if ( strtolower( $table ) === strtolower( $this->wpdb()->get_var( "SHOW TABLES LIKE '$table'" ) ) ) {
				$this->table_exists = true;
			} else {
				$this->table_exists = false;
			}
		}

		if ( ! $this->table_exists ) {

			$wpdb_collate = $this->wpdb()->collate;

			$schema = "CREATE TABLE {$table} (
				ID mediumint(8) unsigned NOT NULL auto_increment,
				remote_id mediumint(8),
				namespace text,
				args longtext,
				steps longtext,
				PRIMARY KEY (ID)
			)
			COLLATE {$wpdb_collate}";

			if ( ! function_exists( 'dbDelta' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			dbDelta( $schema );

		}

	}

	/**
	 * Ensure local table for workflows storage exists
	 * 
	 * @return [type] [description]
	 */
	public function set_local_storage( $items ) {

		$table  = $this->table();
		$query  = "INSERT INTO {$table} (remote_id, namespace, args, steps) VALUES ";
		$values = [];

		foreach( $items as $item ) {

			$remote_id = $item['id'];
			$namespace = $item['namespace'];
			$steps     = maybe_serialize( $item['steps'] );

			unset( $item['id'] );
			unset( $item['namespace'] );
			unset( $item['steps'] );

			$args = maybe_serialize( $item );

			$row = [ $remote_id, $namespace, $args, $steps ];

			$values[] = $this->wpdb()->prepare( "( %d, '%s', '%s', '%s' )", $row );

		}

		$this->wpdb()->query( $query . implode( ', ', $values ) );

	}

	/**
	 * Ensure local table for workflows storage exists
	 * 
	 * @return [type] [description]
	 */
	public function clear_local_storage() {
		$table = $this->table();
		wp_cache_delete( $this->get_storage_key() );
		$this->wpdb()->query( "TRUNCATE $table" );
	}

	/**
	 * Make local storage cache invalid to force workflows list refresh
	 * 
	 * @return [type] [description]
	 */
	public function invalidate_cache() {
		wp_cache_delete( $this->get_storage_key() );
		delete_transient( $this->get_storage_key() );
	}

	/**
	 * Ensure local table for workflows storage exists
	 * 
	 * @return [type] [description]
	 */
	public function get_local_storage() {

		$items = wp_cache_get( $this->get_storage_key() );

		if ( ! $items ) {

			$table = $this->table();
			$items = $this->wpdb()->get_results( "SELECT * FROM {$table};" );

			if ( ! empty( $items ) ) {

				$items = array_map( function( $item ) {

					$prepared_item = [
						'id' => $item->remote_id,
						'namespace' => $item->namespace,
						'steps' => maybe_unserialize( $item->steps ),
					];

					$prepared_item = array_merge( $prepared_item, maybe_unserialize( $item->args ) );

					return $prepared_item;

				}, $items );
			}

			wp_cache_set( $this->get_storage_key(), $items, null, 3 * DAY_IN_SECONDS );

		}

		return $items;

	}

}
