<?php
namespace Jet_Engine\Query_Builder;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define query factory class
 */
class Query_Factory {

	private $type   = null;
	private $config = array();

	private static $_queries = array();

	public function __construct( $data ) {

		$id   = absint( $data['id'] );
		$args = maybe_unserialize( $data['args'] );
		$type = $args['query_type'];

		if ( ! empty( $data['labels'] ) ) {
			$labels = maybe_unserialize( $data['labels'] );
		} else {
			$labels = array( 'name' => 'Preview' );
		}

		$query_config                  = array();
		$query_config['id']            = $id;
		$query_config['name']          = $labels['name'];
		$query_config['type']          = $type;
		$query_config['query_id']      = ! empty( $args['query_id'] ) ? $args['query_id'] : null;
		$query_config['query']         = isset( $args[ $type ] ) ? $args[ $type ] : array();
		$query_config['dynamic_query'] = isset( $args[ '__dynamic_' . $type ] ) ? $args[ '__dynamic_' . $type ] : array();
		$query_config['cache_query']   = isset( $args['cache_query'] ) ? filter_var( $args['cache_query'], FILTER_VALIDATE_BOOLEAN ) : true;
		$query_config['cache_expires'] = isset( $args['cache_expires'] ) ? absint( $args['cache_expires'] ) : 0;

		$query_config['preview'] = array(
			'post_id'      => ! empty( $args['preview_page'] ) ? $args['preview_page'] : false,
			'query_string' => ! empty( $args['preview_query_string'] ) ? $args['preview_query_string'] : '',
		);

		// Prepare API related data
		$query_config['api_endpoint'] = ! empty( $args['api_endpoint'] ) ? $args['api_endpoint'] : false;
		$query_config['api_namespace'] = ! empty( $args['api_namespace'] ) ? $args['api_namespace'] : false;
		$query_config['api_path'] = ! empty( $args['api_path'] ) ? $args['api_path'] : false;
		$query_config['api_access'] = ! empty( $args['api_access'] ) ? $args['api_access'] : false;
		$query_config['api_access_cap'] = ! empty( $args['api_access_cap'] ) ? $args['api_access_cap'] : false;
		$query_config['api_access_role'] = ! empty( $args['api_access_role'] ) ? $args['api_access_role'] : false;
		$query_config['api_schema'] = ! empty( $args['api_schema'] ) ? $args['api_schema'] : false;

		$this->type = $type;
		$this->config = $query_config;

		self::ensure_queries();

	}

	public function get_query() {

		$class = $this->get_query_class( $this->type );

		if ( $class && class_exists( $class ) ) {
			$query = new $class( $this->config );
			$query->maybe_register_rest_api_endpoint();
			
			return $query;
		}

		return false;

	}

	public static function ensure_queries() {

		if ( empty( self::$_queries ) ) {

			require_once Manager::instance()->component_path( 'queries/traits/meta-query.php' );
			require_once Manager::instance()->component_path( 'queries/traits/tax-query.php' );
			require_once Manager::instance()->component_path( 'queries/traits/date-query.php' );

			require_once Manager::instance()->component_path( 'queries/base.php' );
			require_once Manager::instance()->component_path( 'queries/sql.php' );
			require_once Manager::instance()->component_path( 'queries/posts.php' );
			require_once Manager::instance()->component_path( 'queries/terms.php' );
			require_once Manager::instance()->component_path( 'queries/users.php' );
			require_once Manager::instance()->component_path( 'queries/comments.php' );
			require_once Manager::instance()->component_path( 'queries/repeater.php' );
			require_once Manager::instance()->component_path( 'queries/current-wp-query.php' );
			require_once Manager::instance()->component_path( 'queries/merged-query.php' );

			$defaults = array(
				'sql'              => __NAMESPACE__ . '\Queries\SQL_Query',
				'posts'            => __NAMESPACE__ . '\Queries\Posts_Query',
				'terms'            => __NAMESPACE__ . '\Queries\Terms_Query',
				'users'            => __NAMESPACE__ . '\Queries\Users_Query',
				'comments'         => __NAMESPACE__ . '\Queries\Comments_Query',
				'repeater'         => __NAMESPACE__ . '\Queries\Repeater_Query',
				'current-wp-query' => __NAMESPACE__ . '\Queries\Current_WP_Query',
				'merged-query'     => __NAMESPACE__ . '\Queries\Merged_Query',
			);

			foreach ( $defaults as $type => $class ) {
				self::register_query( $type, $class );
			}

			do_action( 'jet-engine/query-builder/queries/register', get_called_class() );

		}

	}

	public static function register_query( $type, $class ) {
		self::$_queries[ $type ] = $class;
	}

	/**
	 * Returns query class name by type
	 *
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function get_query_class( $type ) {
		return isset( self::$_queries[ $type ] ) ? self::$_queries[ $type ] : false;
	}

}
