<?php

namespace Jet_Smart_Filters\Bricks_Views;

use Bricks\Popups;
use Bricks\Query;
use Bricks\Assets;
use Bricks\Helpers;
use Bricks\Database;
use Bricks\Frontend;
use Bricks\Theme_Styles;

/**
 * Query loop bricks provider
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Any custom provider class should extend base provider class
 */
class Provider extends \Jet_Smart_Filters_Provider_Base {

	/**
	 * Allow to add specific query ID to element.
	 * Query ID required if you have 2+ filtered elements of same provider on the page
	 * Example of CSS class with query ID - 'jsfb-query--my-query'. 'jsfb-query--my-query' - its exact query ID.
	 * You need to set the same query ID into appropriate filter Query ID control.
	 * Then this filter will applied only for element with this class
	 * Its optional part implemented in this way for exact provider. Implementation for other providers may be different.
	 * Prefix required because Query Loop element may contain other class which not related to Query ID
	 *
	 * @var string
	 */
	protected $query_id_class_prefix = 'jsfb-query--';

	public $initial_cache_query_loops = false;
	public $base_query_vars = [];

	/**
	 * Add hooks specific for exact provider
	 */
	public function __construct() {
		add_filter( 'bricks/element/set_root_attributes', [ $this, 'set_attributes' ], 999, 2 );
		add_action( 'bricks/query/before_loop', [ $this, 'store_query_props' ], 10 );
	}

	/**
	 * Set prefix for unique ID selector. Mostly is default '#' sign, but sometimes class '.' sign needed.
	 * For example for Query Loop element we don't have HTML/CSS ID attribute, so we need to use class as unique identifier.
	 */
	public function id_prefix() {
		return '.' . $this->query_id_class_prefix;
	}

	/**
	 * Get provider name
	 * @required: true
	 */
	public function get_name() {
		return BRICKS_QUERY_LOOP_PROVIDER_NAME;
	}

	/**
	 * Get provider ID
	 * @required: true
	 */
	public function get_id() {
		return BRICKS_QUERY_LOOP_PROVIDER_ID;
	}

	/**
	 * Check if this providers requires custom renderer on the front-end
	 *
	 * @return [type] [description]
	 */
	public function is_data() {
		return true;
	}

	/**
	 * Set attributes for an element based on its settings.
	 *
	 * @param array $attributes The existing attributes for the element.
	 * @param object $element The element object.
	 * @return array The modified attributes.
	 */
	public function set_attributes( $attributes, $element ) {
		$settings = $element->settings;

		if ( ! isset( $settings['jsfb_is_filterable'] ) || ! Query::is_looping() ) {
			return $attributes;
		}

		// Prepare classes for the filterable element.
		$classes   = [ 'jsfb-filterable' ];
		$query_id  = $settings['jsfb_query_id'] ?? 'default';
		$classes[] = $this->query_id_class_prefix . $query_id;

		return $this->merge_attributes( $attributes, 'class', $classes );
	}

	/**
	 * Merge specified attributes into an existing array of attributes.
	 *
	 * @param array $attributes The existing attributes array.
	 * @param string|null $attr_key The key for the attribute to merge.
	 * @param array $attr_value The array of attribute values to merge.
	 * @return array The modified attributes array.
	 */
	public function merge_attributes( $attributes = [], $attr_key = null, $attr_value = [] ) {
		if ( ! isset( $attributes[ $attr_key ] ) ) {
			$attributes[ $attr_key ] = [];
		}

		// If the attribute value is not an array, convert it to an array.
		if ( ! is_array( $attributes[ $attr_key ] ) ) {
			$attributes[ $attr_key ] = [ $attributes[ $attr_key ] ];
		}

		$attributes[ $attr_key ] = array_merge( $attributes[ $attr_key ], $attr_value );

		return $attributes;
	}

	/**
	 * First of all you need to store default provider query and required attributes to allow
	 * JetSmartFilters attach this data to AJAX request.
	 *
	 * @param array $settings The current settings.
	 * @param object $element The element object.
	 * @return array The modified settings.
	 */
	public function store_query_props( $query ) {
		if ( ! isset( $query->settings['jsfb_is_filterable'] ) ) {
			return;
		}

		if ( ! $this->check_default_object_type( $query->object_type ) ) {
			return;
		}

		$query_id = $query->settings['jsfb_query_id'] ?? 'default';

		// Set the properties
		jet_smart_filters()->query->set_props(
			$this->get_id(),
			[
				'found_posts'   => $query->count,
				'max_num_pages' => $query->max_num_pages,
				'page'          => Query::get_paged_query_var( $query->query_vars ),
			],
			$query_id
		);

		if ( jet_smart_filters()->query->is_ajax_filter() ) {
			return;
		}

		jet_smart_filters()->providers->add_provider_settings(
			$this->get_id(),
			[
				'filtered_post_id'      => $this->get_post_id(),
				'element_id'            => $query->element_id,
				'is_archive_main_query' => isset( $query->settings['query']['is_archive_main_query'] ) ? true : false,
			],
			$query_id
		);

		$query_vars = ! empty( $this->base_query_vars ) ? $this->base_query_vars : $query->query_vars;

		if ( $query->object_type === 'user' ) {
			$query_vars['_query_type'] = 'users';
		}

		jet_smart_filters()->query->store_provider_default_query(
			$this->get_id(),
			$query_vars,
			$query_id
		);
	}

	/**
	 * Get filtered provider content.
	 * @required: true
	 */
	public function ajax_get_content() {
		$settings   = jet_smart_filters()->query->get_query_settings();
		$query_vars = jet_smart_filters()->query->get_query_args();
		$query_id   = jet_smart_filters()->query->get_current_provider( 'query_id' );
		$paged      = $_REQUEST['paged'] ?? 1;

		$post_id          = ( isset( $settings['filtered_post_id'] ) && $settings['filtered_post_id'] !== '' )
			? absint( $settings['filtered_post_id'] )
			: false;
		$query_element_id = ( isset( $settings['element_id'] ) && $settings['element_id'] !== '' )
			? esc_attr( $settings['element_id'] )
			: false;

		if ( false === $post_id || false === $query_element_id ) {
			return;
		}

		Database::$page_data['preview_or_post_id'] = $post_id;

		$bricks_data = Helpers::get_element_data( $post_id, $query_element_id );

		// STEP: Build the flat list index
		$indexed_elements = [];

		foreach ( $bricks_data['elements'] as $element ) {
			$indexed_elements[ $element['id'] ]   = $element;
		}

		// STEP: Set the query element pagination
		$query_element = $indexed_elements[ $query_element_id ];

		$object_type            = $this->get_object_type( $query_element['settings'] );
		$is_default_object_type = $this->check_default_object_type( $object_type );

		if ( $is_default_object_type ) {
			// If the 'is_archive_main_query' setting is not empty, override the main WordPress query
			if ( $object_type === 'post' && ! empty( $settings['is_archive_main_query'] ) ) {
				global $wp_query;
				$wp_query = new \WP_Query( $query_vars );
			}

			if ( in_array( $object_type, [ 'term', 'user' ] ) ) {
				// Don't use request's offset, Term and User query offset should be calculated Query::inside prepare_query_vars_from_settings()
				unset( $query_vars['offset'] );
			}

			// Set the page number which comes from the request
			$query_vars['paged'] = $paged;

			// Set the page number - This is needed for term query
			$query_element['settings']['query']['paged'] = $paged;

			// Add an action to modify the query before it is executed
			add_filter(
				"bricks/{$object_type}s/query_vars",
				function( $vars, $settings, $element_id ) use ( $query_element_id, $query_vars ) {
					if ( $element_id !== $query_element_id ) {
						return $vars;
					}

					// Merge the query vars
					$merged_query_vars = jet_smart_filters()->utils->merge_query_args( $vars, $query_vars );

					return $merged_query_vars;
				}, 10, 3
			);

			if ( $paged > 1 ) {
				// Prevent rendering of query loop trail during JSF "Load More" requests.
				add_filter( 'bricks/render_query_loop_trail', '__return_false' );
			}
		}

		if ( $object_type === 'jet_engine_query_builder' ) {
			// Prevent rendering of query loop trail for JetEngine QB
			add_filter( 'bricks/render_query_loop_trail', '__return_false' );
		}

		// Remove the parent
		if ( ! empty( $query_element['parent'] ) ) {
			$query_element['parent']       = 0;
			$query_element['_noRootClass'] = 1;
		}

		// STEP: Get the query loop elements (main and children)
		$loop_elements = [ $query_element ];

		$children = $query_element['children'];

		while ( ! empty( $children ) ) {
			$child_id = array_shift( $children );

			if ( array_key_exists( $child_id, $indexed_elements ) ) {
				$loop_elements[] = $indexed_elements[ $child_id ];

				if ( ! empty( $indexed_elements[ $child_id ]['children'] ) ) {
					$children = array_merge( $children, $indexed_elements[ $child_id ]['children'] );
				}
			}
		}

		add_filter(
			'bricks/query/no_results_content',
			function ( $content, $settings, $element_id ) use ( $query_element_id ) {
				if ( $element_id !== $query_element_id ) {
					return $content;
				}

				return $this->get_no_results_content( $content, $element_id );
			}, 10, 3
		);

		// Set Theme Styles (for correct preview of query loop nodes)
		Theme_Styles::load_set_styles( $post_id );

		// STEP: Generate the styles again to catch dynamic data changes (eg. background-image)
		$jsf_query_page_id = "jsf_{$query_element_id}";

		Assets::generate_css_from_elements( $loop_elements, $jsf_query_page_id );

		$inline_css = ! empty( Assets::$inline_css[ $jsf_query_page_id ] ) ? Assets::$inline_css[ $jsf_query_page_id ] : '';

		// STEP: Render the element after styles are generated as data-query-loop-index might be inserted through hook in Assets class (@since 1.7.2)
		$html = Frontend::render_data( $loop_elements );

		/**
		 * Fixes Bricks infinite scroll/load more after filtering by reinitializing loops
		 *
		 * Custom Marker to avoid HTML comment removal by plugins
		 * - will be converted to HTML comment in the frontend
		 *
		 * @since 1.12.3 - Bricks
		 */
		if ( $is_default_object_type && $paged === 1 ) {
			$template = "<template style='display:none' aria-hidden='true' data-brx-loop-start='$query_element_id'></template>";
			$html     = $template . $html;
		}

		// Add popup HTML plus styles (@since 1.7.1)
		$popups = Popups::$looping_popup_html;

		// STEP: Add dynamic data styles after render_data() to catch dynamic data changes (eg. background-image) (@since 1.8.2)
		$inline_css .= Assets::$inline_css_dynamic_data;

		$styles = ! empty( $inline_css ) ? "\n<style>/* JSF CSS */\n{$inline_css}</style>\n" : '';

		// STEP: Query data
		$query_data = Query::get_query_by_element_id( $query_element_id );

		// Remove unnecessary properties
		unset( $query_data->settings );
		unset( $query_data->query_result );
		unset( $query_data->loop_index );
		unset( $query_data->loop_object );
		unset( $query_data->is_looping );

		if ( isset( $query_data->query_vars['queryEditor'] ) ) {
			unset( $query_data->query_vars['queryEditor'] );
		}

		if ( isset( $query_data->query_vars['signature'] ) ) {
			unset( $query_data->query_vars['signature'] );
		}

		$rendered_data = [
			'query_id'      => $query_id,
			'element_id'    => $query_element_id,
			'html'          => $html,
			'styles'        => $styles,
			'popups'        => $popups,
			'updated_query' => $query_data,
		];

		add_filter(
			'jet-smart-filters/render/ajax/data',
			function ( $data ) use ( $rendered_data ) {
				return array_merge( $data, $rendered_data );
			}
		);

		if ( $is_default_object_type ) {
			remove_filter( "bricks/{$object_type}s/query_vars", [ $this, 'store_query' ] );
		}
	}

	/**
	 * Apply filters on page reload
	 * Filter arguments in this case passed with $_GET request
	 *
	 * @required: true
	 */
	public function apply_filters_in_request() {
		$query_vars = jet_smart_filters()->query->get_query_args();

		if ( ! $query_vars || jet_smart_filters()->query->is_ajax_filter() ) {
			return;
		}

		add_filter( 'bricks/assets/generate_css_from_element', [ $this, 'modify_bricks_query' ], 10, 2 );
	}


	/**
	 * Here we checking - if will be rendered filtered element - we hook 'bricks/{$object_type}s/query_vars' method
	 * to modify brick query.
	 */
	public function modify_bricks_query( $result, $element ) {
		if ( ! $this->is_filtered_element( $element ) ) {
			return $result;
		}

		$object_type      = $this->get_object_type( $element['settings'] );
		$query_element_id = $element['id'];

		if ( $this->check_default_object_type( $object_type ) ) {
			$query_vars = jet_smart_filters()->query->get_query_args();

			// If the 'is_archive_main_query' setting is not empty, override the main WordPress query
			if ( $object_type === 'post' && isset( $element['settings']['query']['is_archive_main_query'] ) ) {
				global $wp_query;
				$wp_query = new \WP_Query( $query_vars );
			}

			// Add an action to modify the query before it is executed
			add_filter(
				"bricks/{$object_type}s/query_vars",
				function( $vars, $settings, $element_id ) use ( $query_element_id, $query_vars, $object_type ) {
					if ( $element_id !== $query_element_id ) {
						return $vars;
					}

					$this->base_query_vars = $vars;

					if ( $object_type === 'term' ) {
						if ( ! isset( $query_vars['paged'] ) ) {
							$query_vars['paged'] = 1;
						}

						// Pagination: Fix the offset value (@since 1.5)
						$offset = ! empty( $vars['offset'] ) ? $vars['offset'] : 0;

						// If pagination exists, and number is limited (!= 0), use $offset as the pagination trigger
						if ( $query_vars['paged'] !== 1 && ! empty( $vars['number'] ) ) {
							$query_vars['offset'] = ( $query_vars['paged'] - 1 ) * $vars['number'] + $offset;
						}
					}

					if ( $object_type === 'user' ) {
						if ( ! isset( $query_vars['paged'] ) ) {
							$query_vars['paged'] = 1;
						}

						// Pagination: Fix the offset value (@since 1.5)
						$offset = ! empty( $vars['offset'] ) ? $vars['offset'] : 0;

						if ( ! empty( $offset ) && $query_vars['paged'] !== 1 ) {
							$query_vars['offset'] = ( $query_vars['paged'] - 1 ) * $vars['number'] + $offset;
						}
					}

					// Merge the query vars
					$merged_query_vars = jet_smart_filters()->utils->merge_query_args( $vars, $query_vars );

					return $merged_query_vars;
				}, 11, 3
			);
		}

		add_filter(
			'bricks/query/no_results_content',
			function ( $content, $settings, $element_id ) use ( $query_element_id ) {
				if ( $element_id !== $query_element_id ) {
					return $content;
				}

				return $this->get_no_results_content( $content, $element_id );
			}, 10, 3
		);

		return $result;
	}

	/**
	 * Check if is currently filtered brick
	 *
	 * @param array $brick Parsed brick
	 *
	 * @return boolean
	 */
	public function is_filtered_element( $element ) {
		$settings         = $element['settings'] ?? [];
		$element_query_id = $settings['jsfb_query_id'] ?? '';
		$query_id         = jet_smart_filters()->query->get_current_provider( 'query_id' );

		// Bricks Query Loop
		if ( ! isset( $settings['hasLoop'] ) ) {
			return false;
		}

		if ( ! isset( $settings['jsfb_is_filterable'] ) ) {
			return false;
		}

		if ( 'default' === $query_id && empty( $element_query_id ) ) {
			return true;
		}

		return $element_query_id === $query_id;
	}

	/**
	 * Get provider wrapper selector
	 * Its CSS selector of HTML element with provider content.
	 * @required: true
	 */
	public function get_wrapper_selector() {
		return '.jsfb-filterable';
	}

	// Retrieves a query builder instance from the given settings.
	public function get_query_builder_from_settings( $settings ) {
		$query_builder_id = $settings['jet_engine_query_builder_id'] ?? '';

		if ( ! $query_builder_id ) {
			return '';
		}

		return \Jet_Engine\Query_Builder\Manager::instance()->get_query_by_id( $query_builder_id );
	}

	// Retrieves the object type from the given settings.
	public function get_object_type( $settings ) {
		return ! empty( $settings['query']['objectType'] ) ? $settings['query']['objectType'] : 'post';
	}

	// Checks if the given object type is one of the default types.
	public function check_default_object_type( $object_type ) {
		$object_types = [ 'post', 'term', 'user' ];

		foreach ( $object_types as $type ) {
			if ( $type === $object_type ) {
				return true;
			}
		}

		return false;
	}

	// Generates the no results content with specific classes and default message.
	function get_no_results_content( $content, $element_id ) {
		$query_id = jet_smart_filters()->query->get_current_provider( 'query_id' );

		$classes = implode( ' ', [
			'brxe-' . $element_id,
			'jsfb-filterable',
			$this->query_id_class_prefix . $query_id,
		] );

		if ( empty( $content ) ) {
			$content = 'No data was found.';
		}

		return '<div class="' . $classes . '">' . $content . '</div>';
	}

	/**
	 * Retrieves the post ID based on the current page data and active template settings.
	 *
	 * @return int|string The resolved post ID.
	 */
	public function get_post_id() {
		$post_id               = Database::$page_data['original_post_id'] ?? Database::$page_data['preview_or_post_id'];
		$template_content_type = Database::$active_templates['content_type'] ?? '';

		if ( $template_content_type === 'archive' ) {
			$post_id = Database::$active_templates['content'];
		}

		if ( $template_content_type === 'search' ) {
			$post_id = Database::$active_templates['search'];
		}

		return $post_id;
	}
}