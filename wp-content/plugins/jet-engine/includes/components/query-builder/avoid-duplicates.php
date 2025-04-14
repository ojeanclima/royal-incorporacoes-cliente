<?php
namespace Jet_Engine\Query_Builder;
/**
 * Avoid Duplicates manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Avoid_Duplicates class
 */
class Avoid_Duplicates {

	private static $instance = null;

	private $watch_posts = false;
	public $can_save_posts = true;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @access public
	 * @static
	 *
	 * @return static An instance of the class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function watch_posts() {
		$this->watch_posts = true;
	}

	public function is_watching_posts() {
		return $this->watch_posts;
	}

	private function __construct() {
		add_action( 'jet-engine/query-builder/after-queries-setup', array( $this, 'add_hooks' ) );
	}

	/**
	 * Add necessary hooks
	 */
	public function add_hooks() {
		if ( ! $this->is_watching_posts() ) {
			return;
		}

		add_filter( 'jet-engine/query-builder/query/items', array( $this, 'save_shown_posts' ), 10, 2 );
		add_filter( 'jet-engine/query-builder/types/posts-query/args', array( $this, 'adjust_query_args' ), 1000, 2 );
		add_filter( 'jet-engine/query-builder/wc-product-query/args', array( $this, 'adjust_query_args' ), 1000, 2 );
		add_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'maybe_disable_caching' ) );
	}

	/**
	 * Save shown posts
	 * 
	 * @param object[]           $posts Array of posts returned by query
	 * @param Queries\Base_Query $query Query object
	 */
	public function save_shown_posts( $posts, $query ) {
		if ( ! $this->can_save_posts || ! $this->is_supported_query( $query ) ) {
			return $posts;
		}

		/**
		 * @var \WP_Post|\WC_Product $post
		 */
		foreach ( $posts as $post ) {
			$id = jet_engine()->listings->data->get_current_object_id( $post );
			
			if ( ! $id ) {
				continue;
			}

			jet_engine()->listings->did_posts->set_post_as_shown( $id );
		}

		return $posts;
	}

	/**
	 * Adjust query args
	 * 
	 * @param array              $args  Query args
	 * @param Queries\Base_Query $query Query object
	 */
	public function adjust_query_args( $args, $query ) {
		if ( ! $this->is_avoid_duplicates_enabled( $query ) ) {
			return $args;
		}

		switch ( $query->query_type ) {
			case 'posts':
				$post__in_key     = 'post__in';
				$post__not_in_key = 'post__not_in';
				break;
			case 'wc-product-query':
				$post__in_key     = 'include';
				$post__not_in_key = 'exclude';
				break;
			default:
				return $args;
		}

		$args[ $post__in_key ]     = ! empty( $args[ $post__in_key ] ) ? $args[ $post__in_key ] : array();
		$args[ $post__not_in_key ] = ! empty( $args[ $post__not_in_key ] ) ? $args[ $post__not_in_key ] : array();

		$args[ $post__not_in_key ] = array_merge(
			$args[ $post__not_in_key ],
			jet_engine()->listings->did_posts->get_shown_posts()
		);

		if ( ! empty( $args[ $post__in_key ] ) ) {
			$args[ $post__in_key ] = array_diff( $args[ $post__in_key ], $args[ $post__not_in_key ] );
		}

		return $args;
	}

	/**
	 * Disable cahing if Avoid duplicates is enabled
	 * 
	 * @param Queries\Base_Query $query Query object
	 */
	public function maybe_disable_caching( $query ) {
		if ( $this->is_avoid_duplicates_enabled( $query ) ) {
			$query->cache_query = false;
		}
	}

	/**
	 * Check if query is supported
	 * 
	 * @param Queries\Base_Query $query Query object
	 */
	public function is_supported_query( $query ) {
		return in_array( $query->query_type ?? false, array( 'posts', 'wc-product-query' ) );
	}

	/**
	 * Check if avoid duplicates is enabled
	 * 
	 * @param Queries\Base_Query $query Query object
	 */
	public function is_avoid_duplicates_enabled( $query ) {
		$avoid_duplicates = filter_var(
			$query->final_query['avoid_duplicates'] ?? false,
			FILTER_VALIDATE_BOOLEAN
		);
		
		return $avoid_duplicates && $this->is_supported_query( $query );
	}

	/**
	 * Get control template
	 * 
	 * @param string $item_type Item type
	 */
	public function print_control( $item_type ) {

		if ( ! apply_filters( 'jet-engine/query-builder/avoid-duplicates/add-controls', true ) ) {
			return;
		}

		switch ( $item_type ) {
			case 'posts':
				$description = __( 'Avoid post duplicates. Does not work with JetSmartFilters AJAX filtering, Load More, Lazy Load, and any other feature that uses AJAX loading. Caching for this query will be disabled.', 'jet-engine' );
				break;
			case 'products':
				$description = __( 'Avoid product duplicates. Does not work with JetSmartFilters AJAX filtering, Load More, Lazy Load, and any other feature that uses AJAX loading. Caching for this query will be disabled.', 'jet-engine' );
				break;
		}

		?>
		
		<cx-vui-switcher
			:label="'<?php _e( 'Avoid Duplicates', 'jet-engine' ); ?>'"
			:description="'<?php echo $description; ?>'"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			v-model="query.avoid_duplicates"
		></cx-vui-switcher>

		<?php
	}
}
