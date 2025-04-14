<?php

namespace Jet_Engine\Bricks_Views;

use Bricks\Api;
use Bricks\Query;
use Bricks\Database;
use Jet_Engine\Bricks_Views\Helpers\Options_Converter;
use Jet_Engine\Query_Builder\Manager as Query_Manager;

class Query_Loop {

	public $initial_object = [];
	public $initial_popup_object = null;
	public $listing_stack = array();

	function __construct() {
		// Bricks loop with default object types
		add_action( 'bricks/query/before_loop', array( $this, 'initialize_object_before_render' ), 10 );
		add_action( 'bricks/query/after_loop', array( $this, 'restore_initial_object_after_render' ), 10 );
		add_filter( 'jet-engine/listings/data/the-post/is-main-query', array( $this, 'maybe_modify_is_main_query' ) );

		// Bricks loop with Query builder
		add_action( 'init', array( $this, 'add_control_to_elements' ), 40 );
		add_filter( 'bricks/setup/control_options', array( $this, 'setup_query_controls' ) );

		add_filter( 'bricks/query/run', array( $this, 'run_query' ), 10, 2 );
		add_filter( 'bricks/query/result_count', array( $this, 'set_count' ), 10, 2 );
		add_filter( 'bricks/query/result_max_num_pages', array( $this, 'set_max_num_pages' ), 10, 2 );
		add_filter( 'bricks/query/init_loop_index', array( $this, 'initialize_loop_index' ), 10, 3 );
		add_filter( 'bricks/query/loop_object', array( $this, 'set_loop_object' ), 10, 3 );

		// Initialize current listing for Bricks loop with default object types and Query builder.
		add_action( 'bricks/query/before_loop', array( $this, 'initialize_current_listing' ), 10 );
		add_action( 'bricks/query/after_loop', array( $this, 'restore_current_listing' ), 10 );

		// Ajax-powered popup inside a Bricks loop item
		add_action( 'bricks/frontend/before_render_data', array( $this, 'initialize_object_before_popup_render' ), 10, 2 );
		add_action( 'bricks/frontend/after_render_data', array( $this, 'restore_object_after_popup_render' ), 10, 2);

		add_filter( 'bricks/element/settings', array( $this, 'manage_stack_for_bricks_loop' ), 10, 2 );

		// JetEngine shortcode inside a Bricks loop item
		add_filter( 'jet-engine/listings/data/default-object', array( $this, 'maybe_set_bricks_loop_object' ) );
	}

	/**
	 * Initialize the object before rendering.
	 *
	 * @param object $query The query object containing details about the query.
	 */
	public function initialize_object_before_render( $query ) {

		if ( in_array( $query->object_type, [ 'user', 'term' ] ) ) {
			add_action( 'jet-engine/listing-element/before-render', array( $this, 'set_current_object' ) );
		}

		$this->initial_object[ $query->element_id ] = jet_engine()->listings->data->get_current_object();
	}

	/**
	 * Restore the initial object after rendering.
	 *
	 * @param object $query The query object containing details about the query.
	 */
	public function restore_initial_object_after_render( $query ) {

		if ( in_array( $query->object_type, [ 'user', 'term' ] ) ) {
			remove_action( 'jet-engine/listing-element/before-render', array( $this, 'set_current_object' ) );
		}

		if ( ! empty( $this->initial_object[ $query->element_id ] ) ) {
			jet_engine()->listings->data->set_current_object( $this->initial_object[ $query->element_id ] );
			unset( $this->initial_object[ $query->element_id ] );
		}
	}

	// Set current User or Term object to dynamic widgets in a Bricks loop
	public function set_current_object() {
		jet_engine()->listings->data->set_current_object( Query::get_loop_object() );
	}

	/**
	 * Modify the main query under certain conditions.
	 *
	 * @param bool   $is_main_query  Whether the query is the main query.
	 * @param object $post           The current post object.
	 * @param object $query          The current WP_Query object.
	 *
	 * @return bool  Modified value for $is_main_query.
	 */
	public function maybe_modify_is_main_query( $is_main_query ) {
		$content_type = Database::$active_templates['content_type'] ?? '';

		if ( $is_main_query && $content_type === 'archive' ) {
			return ! $is_main_query;
		}

		return $is_main_query;
	}

	public function add_control_to_elements() {
		// Only container, block and div element have query controls
		$elements = [ 'section', 'container', 'block', 'div' ];

		foreach ( $elements as $name ) {
			add_filter( "bricks/elements/{$name}/controls", [ $this, 'add_jet_engine_controls' ], 40 );
		}
	}

	public function add_jet_engine_controls( $controls ) {
		$options = \Jet_Engine\Query_Builder\Manager::instance()->get_queries_for_options();

		// jet_engine_query_builder_id will be my option key
		$jet_engine_control['jet_engine_query_builder_id'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'JetEngine Queries', 'jet-engine' ),
			'type'        => 'select',
			'options'     => Options_Converter::remove_empty_key_in_options( $options ),
			'placeholder' => esc_html__( 'Choose a query', 'jet-engine' ),
			'required'    => array(
				[ 'query.objectType', '=', 'jet_engine_query_builder' ],
				[ 'hasLoop', '!=', false ]
			),
			'rerender'    => true,
			'description' => esc_html__( 'Please create a query in JetEngine Query Builder First', 'jet-engine' ),
			'searchable'  => true,
			'multiple'    => false,
		];

		// Below 2 lines is just some php array functions to force my new control located after the query control
		$query_key_index = absint( array_search( 'query', array_keys( $controls ) ) );
		$new_controls    = array_slice( $controls, 0, $query_key_index + 1, true ) + $jet_engine_control + array_slice( $controls, $query_key_index + 1, null, true );

		return $new_controls;
	}

	public function setup_query_controls( $control_options ) {
		// Add a new query loop type
		$control_options['queryTypes']['jet_engine_query_builder'] = esc_html__( 'JetEngine Query Builder', 'jet-engine' );

		return $control_options;
	}

	public function run_query( $results, $query ) {
		if ( ! $this->is_jet_engine_query( $query ) ) {
			return $results;
		}

		$query->add_to_history();

		$query_id = apply_filters( 'jet-engine/bricks-views/query-builder/query-id', $this->get_jet_engine_query_id( $query->settings ) );

		// Return empty results if no query selected or Use Query is not checked
		if ( $query_id === 0 ) {
			return $results;
		}

		$je_query = Query_Manager::instance()->get_query_by_id( $query_id );

		// Return empty results if query not found in JetEngine Query Builder
		if ( ! $je_query ) {
			return $results;
		}

		// Setup query args
		$je_query->setup_query();

		$paged = $query->query_vars['paged'] ?? 1;

		if ( $paged > 1 ) {
			$je_query->set_filtered_prop( '_page', $paged );
		}

		do_action( 'jet-engine/bricks-views/query-builder/on-query', $je_query, $query->element_id );

		// Get the results
		return $je_query->get_items();
	}

	public function set_count( $count, $query ) {
		if ( ! $this->is_jet_engine_query( $query ) ) {
			return $count;
		}

		$je_query = $this->get_jet_engine_query( $query->settings );

		// Return empty results if query not found in JetEngine Query Builder
		if ( ! $je_query ) {
			return $count;
		}

		return $je_query->get_items_total_count();
	}

	public function set_max_num_pages( $max_num_pages, $query ) {
		if ( ! $this->is_jet_engine_query( $query ) ) {
			return $max_num_pages;
		}

		$je_query = $this->get_jet_engine_query( $query->settings );

		// Return empty results if query not found in JetEngine Query Builder
		if ( ! $je_query ) {
			return $max_num_pages;
		}

		return $je_query->get_items_pages_count();
	}

	/**
	 * Calculates the initial loop index for Jet Engine queries.
	 *
	 * @param int $index The default loop index passed through the filter.
	 * @param string $object_type The type of the query object (e.g., 'posts', 'terms', 'users').
	 * @param object $query The query object containing the query variables and settings.
	 * @return int The calculated initial loop index.
	 */
	function initialize_loop_index( $index, $object_type, $query ) {
		if ( ! $this->is_jet_engine_query( $query ) ) {
			return $index;
		}

		$je_query = $this->get_jet_engine_query( $query->settings );

		if ( ! $je_query ) {
			return $index;
		}

		$query_args     = $je_query->get_query_args();
		$paged          = ! empty( $query_args['paged'] ) ? $query_args['paged'] : 1;
		$offset         = isset( $query_args['offset'] ) ? intval( $query_args['offset'] ) : 0;
		$posts_per_page = $je_query->get_items_per_page();

		switch ( $query_args['_query_type'] ) {
			// Post loop and User loop
			case 'posts':
			case 'users':
				$initial_index = $offset + ( $posts_per_page > 0 ? ( $paged - 1 ) * $posts_per_page : 0 );
				break;

			// Term loop and CCT
			case 'terms':
			case 'custom-content-type':
				$initial_index = $offset;
				break;
			default:
				$initial_index = $index;
				break;
		}

		return $initial_index;
	}

	public function set_loop_object( $loop_object, $loop_key, $query ) {
		if ( ! $this->is_jet_engine_query( $query ) ) {
			return $loop_object;
		}

		global $post;

		// I only tested on JetEngine Posts Query, Terms Query, Comments Query and WC Products Query
		// I didn't set WP_Term condition because it's not related to the $post global variable
		if ( is_a( $loop_object, 'WP_Post' ) ) {
			$post = $loop_object;
		} elseif ( is_a( $loop_object, 'WC_Product' ) ) {
			// $post should be a WP_Post object
			$post = get_post( $loop_object->get_id() );
		} elseif ( is_a( $loop_object, 'WP_Comment' ) ) {
			// A comment should refer to a post, so I set the $post global variable to the comment's post
			// You might want to change this to $loop_object->comment_ID
			$post = get_post( $loop_object->comment_post_ID );
		}

		setup_postdata( $post );

		$je_query = $this->get_jet_engine_query( $query->settings );

		// Return empty results if query not found in JetEngine Query Builder
		if ( ! $je_query ) {
			return $loop_object;
		}

		// Set current object for JetEngine
		jet_engine()->listings->data->set_current_object( $loop_object );

		// We still return the $loop_object so \Bricks\Query::get_loop_object() can use it
		return $loop_object;
	}

	/**
	 * Initialize the current listing based on the provided query.
	 *
	 * @param object $query The query object containing the query variables.
	 */
	public function initialize_current_listing( $query ) {
		// Check if the query is a Jet Engine Query Builder request
		if ( $this->is_jet_engine_query( $query ) ) {
			// Set the listing data for Jet Engine Query
			$listing_data = array(
				'listing_source' => 'query',
				'_query_id'      => $this->get_jet_engine_query_id( $query->settings ),
			);
		} else {
			// Get the source from the Bricks query loop
			$source = $this->get_bricks_query_object_type( $query );

			// If a source exists, populate the listing data with new values
			if ( $source ) {
				$query_vars  = $query->query_vars;
				$post_type   = ! empty( $query_vars['post_type'] ) ? $query_vars['post_type'][0] : 'post';
				$tax         = ! empty( $query_vars['taxonomy'] ) ? $query_vars['taxonomy'][0] : 'category';

				$listing_data = array(
					'listing_source'    => $source,
					'listing_post_type' => $post_type,
					'listing_tax'       => $tax,
				);
			}
		}

		// If no listing data was set, exit the function early
		if ( empty( $listing_data ) ) {
			return;
		}

		if ( empty( $this->listing_stack ) ) {
			$this->listing_stack['root'] = jet_engine()->listings->data->get_listing();
		}

		// Create a new document and set the current listing
		$doc = jet_engine()->listings->get_new_doc( $listing_data, 0 );
		jet_engine()->listings->data->set_listing( $doc );

		if ( ! array_key_exists( $query->element_id, $this->listing_stack ) ) {
			$this->listing_stack[ $query->element_id ] = $doc;
		}
	}

	/**
	 * Restore the current listing based on the provided query.
	 *
	 * @param object $query The query object containing the query variables.
	 */
	public function restore_current_listing( $query ) {
		// If this is neither a Jet Engine query nor a Bricks query object type, exit the function
		if ( ! $this->is_jet_engine_query( $query ) && ! $this->get_bricks_query_object_type( $query ) ) {
			return;
		}

		// If element_id does not exist in the stack, exit
		if ( ! isset( $this->listing_stack[ $query->element_id ] ) ) {
			return;
		}

		// Remove the element from the stack
		unset( $this->listing_stack[ $query->element_id ] );

		// Get previous element key
		$keys = array_keys( $this->listing_stack );
		$previous_key = end( $keys );

		// Restore the last element
		jet_engine()->listings->data->set_listing( $this->listing_stack[ $previous_key ] );
	}

	/**
	 * Retrieve the JetEngine query object based on the provided settings.
	 *
	 * @param array $settings The settings array containing the query builder ID.
	 * @return mixed Returns the JetEngine query object if found, or false if no valid query ID.
	 */
	public function get_jet_engine_query( $settings ) {
		$query_id = $this->get_jet_engine_query_id( $settings );

		// Return empty results if no query selected or Use Query is not checked
		if ( $query_id === 0 ) {
			return false;
		}

		// Get the query object from JetEngine based on the query id
		return Query_Manager::instance()->get_query_by_id( $query_id );
	}

	/**
	 * Retrieve the JetEngine query ID from the given settings array.
	 *
	 * @param array $settings The settings array containing the query builder ID.
	 * @return int The query ID as an integer, or 0 if not found or empty.
	 */
	public function get_jet_engine_query_id( $settings ) {
		return ! empty( $settings['jet_engine_query_builder_id'] ) ? absint( $settings['jet_engine_query_builder_id'] ) : 0;
	}

	/**
	 * Check if the provided query object is a Jet Engine Query Builder query.
	 *
	 * @param object $query The query object to validate.
	 * @return bool Returns true if the query is valid, false otherwise.
	 */
	public function is_jet_engine_query( $query ) {
		if ( $query->object_type !== 'jet_engine_query_builder' || ! $query->settings['hasLoop'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the default bricks loop source based on the object type.
	 *
	 * @param object $query The query object containing the object_type property.
	 * @return string|false The corresponding source for the bricks loop, or false if not found.
	 */
	public function get_bricks_query_object_type( $query ) {
		$source_mapping = array(
			'post' => 'posts',
			'term' => 'terms',
			'user' => 'users'
		);

		return $source_mapping[ $query->object_type ] ?? false;
	}

	/**
	 * Sets the queried object for rendering JetEngine dynamic widgets in a popup.
	 *
	 * @param array  $elements Array of elements to be rendered.
	 * @param string $area     The area where the elements will be rendered.
	 *
	 * @return void
	 */
	public function initialize_object_before_popup_render( $elements, $area ) {
		if ( $area !== 'popup' || ! $this->is_ajax_popup_looping() ) {
			return;
		}

		/*$this->initial_popup_object = jet_engine()->listings->data->get_current_object();*/

		jet_engine()->listings->data->set_current_object( get_queried_object() );
	}

	/**
	 * Sets the initial object after popup rendering.
	 *
	 * @param array  $elements Array of elements to be rendered.
	 * @param string $area     The area where the elements will be rendered.
	 *
	 * @return void
	 */
	public function restore_object_after_popup_render( $elements, $area ) {
		if ( $area !== 'popup' || ! $this->is_ajax_popup_looping() ) {
			return;
		}

		// Set initial object for generating dynamic style in Listing grid
		/*if ( ! empty( $this->initial_popup_object ) ) {
			jet_engine()->listings->data->set_current_object( $this->initial_popup_object );
		}*/
	}

	/**
	 * Checks if the AJAX popup is currently in a looping state.
	 *
	 * @return bool Returns true if the popup is in a looping state, false otherwise.
	 */
	public function is_ajax_popup_looping() {
		if ( ! Api::is_current_endpoint( 'load_popup_content' ) ) {
			return false;
		}

		$request_data     = jet_engine()->bricks_views->get_request_data();
		$is_looping       = $request_data['isLooping'] ?? '';
		$popup_context_id = $request_data['popupContextId'] ?? '';

		if ( empty( $popup_context_id ) || empty( $is_looping ) ) {
			return false;
		}

		return true;
	}

	// Set popup object to dynamic widgets in a Popup
	public function set_popup_object() {
		jet_engine()->listings->data->set_current_object( get_queried_object() );
	}

	public function manage_stack_for_bricks_loop( $settings, $element ) {
		if ( ! isset( $settings['hasLoop'] ) ) {
			return $settings;
		}

		$object_type = $this->get_object_type( $settings );
		$query_id    = $this->get_jet_engine_query_id( $settings );

		if ( $object_type === 'jet_engine_query_builder' && $query_id !== 0 ) {
			add_filter( 'bricks/query/loop_object', array( $this, 'add_query_builder_object_to_stack' ) );
		} elseif( $object_type === 'post' ) {
			add_action( 'the_post', array( $this, 'add_bricks_loop_object_to_stack' ) );
		} else {
			return $settings;
		}

		add_filter( 'bricks/dynamic_data/render_content', array( $this, 'remove_object_from_stack' ), 10, 2 );

		return $settings;
	}

	/**
	 * Add object to the stack
	 *
	 * @param  [type] $object [description]
	 * @return [type]         [description]
	 */
	public function add_to_stack( $object ) {
		do_action( 'jet-engine/object-stack/increase', $object );
	}

	public function add_query_builder_object_to_stack( $object ) {
		$this->add_to_stack( $object );

		return $object;
	}

	public function add_bricks_loop_object_to_stack( $object ) {
		if ( Query::is_looping() ) {
			$this->add_to_stack( $object );
		}
	}

	/**
	 * Remove object from the stack
	 *
	 * @param  [type] $object [description]
	 * @return [type]         [description]
	 */
	public function remove_from_stack( $object ) {
		do_action( 'jet-engine/object-stack/decrease', $object );
	}

	public function remove_object_from_stack( $content, $object ) {
		$this->remove_from_stack( $object );

		return $content;
	}

	public function get_object_type( $settings ) {
		return ! empty( $settings['query']['objectType'] ) ? $settings['query']['objectType'] : 'post';
	}

	/**
	 * Sets the default object in the Bricks loop during an API request.
	 *
	 * @param mixed $object The current loop object.
	 * @return mixed The Bricks loop object or the original object.
	 */
	function maybe_set_bricks_loop_object( $object ) {
		// Check if no object is set, Bricks is looping, and it's an API request
		if ( ! $object && \Bricks\Query::is_looping() && $this->is_bricks_api_request() ) {
			$looping_query_id = \Bricks\Query::is_any_looping();
			$object           = \Bricks\Query::get_loop_object( $looping_query_id );
		}

		return $object;
	}

	/**
	 * Checks if the current request is a Bricks API request.
	 *
	 * @return bool True if the request is to Bricks API.
	 */
	function is_bricks_api_request() {
		$bricks_request_path = 'wp-json/bricks/v1/render_element';
		$request_uri         = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		// Check if the request URI contains the API path at the end
		return ! empty( $request_uri ) && str_ends_with( rtrim( $request_uri, '/' ), $bricks_request_path );
	}
}
