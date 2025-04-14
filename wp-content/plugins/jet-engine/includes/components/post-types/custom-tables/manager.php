<?php
namespace Jet_Engine\CPT\Custom_Tables;

/**
 * @property Preset form_preset
 *
 * Class Module
 * @package Jet_Engine\Modules\Custom_Content_Types
 */
class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public $storages = [];
	public $suffix = '_meta';

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'jet-engine/post-types/registered', [ $this, 'init' ] );
	}

	/**
	 * Make sure DB class is correctlry loaded
	 * @return [type] [description]
	 */
	public function ensure_has_db_class() {
		if ( ! class_exists( '\Jet_Engine\CPT\Custom_Tables\DB' ) ) {
			require_once jet_engine()->cpt->component_path( 'custom-tables/db.php' );
		}
	}

	/**
	 * Initialize all handlers for registered CPTs
	 * @return [type] [description]
	 */
	public function init() {

		$this->ensure_has_db_class();
		
		if ( empty( $this->storages ) ) {
			return;
		}

		require_once jet_engine()->cpt->component_path( 'custom-tables/meta-storage.php' );
		require_once jet_engine()->cpt->component_path( 'custom-tables/meta-query.php' );
		require_once jet_engine()->cpt->component_path( 'custom-tables/query.php' );

		foreach ( $this->storages as $data ) {

			$object_type = $data['object_type'];
			$object_slug = $data['object_slug'];

			$db = $this->get_db_instance( $object_slug, $data['fields'] );

			new Meta_Storage( $db, $object_type, $object_slug, $data['fields'] );
			new Query( $db, $object_type, $object_slug, $data['fields'] );

		}

		add_filter( 'posts_clauses', [ $this, 'add_posts_clauses' ], 10, 2 );

	}

	/**
	 * Public function get table name from oject slug
	 * @return [type] [description]
	 */
	public function get_table_name( $slug = '' ) {

		$table_name = str_replace( '-', '_', $slug );

		return apply_filters( 
			'jet-engine/custom-meta-tables/table-name-for-object-slug',
			$table_name . $this->suffix,
			$slug
		);

	}

	/**
	 * Returns a DB manager instance for given config
	 * 
	 * @param  string $object_slug              Object slug
	 * @param  array  $fields                   Array of fields (columns)
	 * 
	 * @return \Jet_Engine\CPT\Custom_Tables\DB DB instance
	 */
	public function get_db_instance( $object_slug, $fields = [] ) {

		$this->ensure_has_db_class();

		$schema = [
			'meta_ID'   => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'object_ID' => 'bigint(20)',
		];

		if ( ! empty( $fields ) ) {
			foreach( $fields as $field ) {
				$schema[ $field ] = false;
			}
		}

		$schema = apply_filters(
			'jet-engine/custom-meta-tables/object-schema/' . $object_slug,
			$schema
		);

		return new DB( $this->get_table_name( $object_slug ), $schema );

	}

	/**
	 * Add custom posts query clauses to default WP query
	 * 
	 * @param [type] $clauses [description]
	 * @param [type] $query   [description]
	 */
	public function add_posts_clauses( $clauses, $query ) {

		$custom_table_query = $query->get( 'custom_table_query' );

		if ( $custom_table_query ) {

			global $wpdb;

			$custom_query = new Meta_Query( $custom_table_query['query'] );
			$custom_query->set_custom_table( $custom_table_query['table'] );
			$custom_clauses = $custom_query->get_sql( 'post', $wpdb->posts, 'ID', $query );

			if ( ! empty( $custom_clauses['join'] ) ) {
				$clauses['join'] .= $custom_clauses['join'];
			}

			if ( ! empty( $custom_clauses['where'] ) ) {
				$clauses['where'] .= $custom_clauses['where'];
			}

			$clauses['fields'] .= ', ' . $custom_table_query['table'] . '.*';

			if ( ! empty( $custom_table_query['order'] ) ) {
				foreach( $custom_table_query['order'] as $order_by => $order ) {
					$clauses['orderby'] .= sprintf( ', %1$s.%2$s %3$s', $custom_table_query['table'], $order_by, $order );
					$clauses['orderby'] = ltrim( $clauses['orderby'], ',' );
				}
			}

		}

		return $clauses;
	}

	/**
	 * Register new custom sotrage
	 * 
	 * @param  string $post_type [description]
	 * @param  string $table     [description]
	 * @param  array  $fields    [description]
	 * @return [type]            [description]
	 */
	public function register_storage( $object_type = 'post', $object_slug = '', $fields = [] ) {

		$fields_data = $this->prepare_fields( $fields );

		$this->storages[] = apply_filters( 'jet-engine/custom-meta-tables/storage-data', [
			'object_type' => $object_type,
			'object_slug' => $object_slug,
			'fields'      => $fields_data['as_columns'],
			'raw_fields'  => $fields_data['raw'],
		], $this );

	}

	/**
	 * Ensure fields will be registered in correct format
	 * 
	 * @param  array  $fields [description]
	 * @return array  $prepared_fields        [description]
	 */
	public function prepare_fields( $fields = [] ) {

		$prepared_fields = [
			'as_columns' => [],
			'raw'        => [],
		];

		if ( ! empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( is_string( $field ) ) {
					$prepared_fields['as_columns'][] = $this->sanitize_field_name( $field );
					$prepared_fields['raw'][] = [
						'field_name' => $field,
						'type'       => 'text',
					];
				} elseif ( is_array( $field ) && isset( $field['name'] ) ) {
					if ( ! isset( $field['object_type'] ) || 'field' === $field['object_type'] ) {
						$prepared_fields['as_columns'][] = $this->sanitize_field_name( $field['name'] );
						$prepared_fields['raw'][] = [
							'name' => $field['name'],
							'type' => $field['type'],
						];
					}
				}
			}
		}

		$prepared_fields = apply_filters( 'jet-engine/custom-meta-tables/prepared_fields', $prepared_fields );

		$prepared_fields['as_columns'] = array_unique( $prepared_fields['as_columns'], SORT_STRING );

		return $prepared_fields;
	}

	/**
	 * Ensure field name can be used as column
	 * 
	 * @param  string $field [description]
	 * @return [type]        [description]
	 */
	public function sanitize_field_name( $field = '' ) {

		$field = str_replace( '-', '_', $field );

		// Remove any characters that are not alphanumeric or underscore
		$field = str_replace(
			[ '(', ')', '{', '}', '<', '>', ' ', '.', ',', ';', '=', '+', '*', '&', '^', '%', '$', '#', '@', '!', '~', '`', '?', '/', '\\'], 
			'', 
			$field
		);

		// Ensure the column name is not empty
		if ( empty( $field ) ) {
			return false;
		}

		// Ensure the column name does not start with a number
		if ( ctype_digit( substr( $field, 0, 1 ) ) ) {
			// If the column name starts with a number, prefix it with an underscore or another character
			$field = '_' . $field;
		}

		// Return the sanitized column name
		return $field;

	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return \Jet_Engine\CPT\Custom_Tables\Manager
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
