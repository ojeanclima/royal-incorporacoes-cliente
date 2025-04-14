<?php
/**
 * Bricks views manager
 */

namespace Jet_Engine\Bricks_Views\Listing;

/**
 * Define render class
 */
class Render {

	protected $current_query;
	protected $current_listing_id = null;

	public function __construct() {
		add_filter( 'jet-engine/listing/content/bricks', array( $this, 'get_listing_content_cb' ), 10, 2 );
		add_filter( 'jet-engine/listing/pre-get-item-content', array( $this, 'set_initial_listing_id' ), 9, 4 );
		add_filter( 'jet-engine/listing/grid/columns', array( $this, 'remap_columns' ), 10, 2 );

		add_action( 'jet-engine/listing/grid/before-render', array( $this, 'set_query_on_render' ) );
		add_action( 'jet-engine/listing/grid/after-render', array( $this, 'destroy_bricks_query' ) );

		add_action( 'jet-smart-filters/render/ajax/before', array( $this, 'set_query_on_filters_ajax' ) );
		add_action( 'jet-engine/ajax-handlers/before-do-ajax', array( $this, 'set_query_on_listing_ajax' ), 10, 2 );

		add_filter( 'jet-engine/listing/render/default-settings', array( $this, 'add_default_settings' ) );
		add_filter( 'jet-engine/listing/grid/nav-widget-settings', array( $this, 'add_widget_settings' ), 10, 2 );
	}

	public function set_bricks_query( $listing_id = 0, $settings = [] ) {
		if ( ! $listing_id ) {
			$listing_id = isset( $settings['lisitng_id'] ) ? absint( $settings['lisitng_id'] ) : 0;
		}

		if ( ! $listing_id || ! jet_engine()->bricks_views->is_bricks_listing( $listing_id ) ) {
			return;
		}

		if ( isset( $this->current_query[ $listing_id ] ) ) {
			return;
		}

		$is_component      = jet_engine()->listings->components->is_component( $listing_id );
		$has_dynamic_value = ! $is_component && jet_engine()->bricks_views->listing->has_dynamic_value_in_controls( $listing_id );

		$id = ( ( $is_component || $has_dynamic_value ) && isset( $settings['_id'] ) ) ? $settings['_id'] : '';

		$settings['set_query_for_listing_id'] = $listing_id;

		$this->current_query[ $listing_id ] = jet_engine()->bricks_views->listing->get_bricks_query( [
			'id'       => $id,
			'settings' => $settings,
		] );
	}

	/**
	 * Retrieves the current query for a given listing ID.
	 *
	 * @param int $listing_id The ID of the listing to get the current query for.
	 * @return mixed The current query for the given listing ID, or false if not found.
	 */
	public function get_current_query( $listing_id ) {
		if ( isset( $this->current_query[ $listing_id ] ) ) {
			return $this->current_query[ $listing_id ];
		}

		if ( ! empty( $this->current_listing_id ) && isset( $this->current_query[ $this->current_listing_id ] ) ) {
			return $this->current_query[ $this->current_listing_id ];
		}

		return false;
	}

	public function set_query_on_filters_ajax() {
		$settings   = isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : [];
		$listing_id = ! empty ( $settings['lisitng_id'] ) ? $settings['lisitng_id'] : 0;
		$this->set_bricks_query( $listing_id, $settings );
	}

	public function set_query_on_listing_ajax( $ajax_handler, $request ) {
		$settings   = $request['widget_settings'] ?? $request['settings'] ?? [];
		$listing_id = ! empty ( $settings['lisitng_id'] ) ? $settings['lisitng_id'] : 0;
		$this->set_bricks_query( $listing_id, $settings );
	}

	public function set_query_on_render( $render ) {
		$listing_id = $render->get_settings( 'lisitng_id' );
		$this->set_bricks_query( $listing_id, $render->get_settings() );
	}

	public function destroy_bricks_query( $render ) {
		$listing_id = $render->get_settings( 'lisitng_id' );
		
		if ( $listing_id ) {
			$this->destroy_bricks_query_for_listing( $listing_id );
		}
	}

	public function destroy_bricks_query_for_listing( $listing_id ) {

		$current_query = $this->get_current_query( $listing_id );

		if ( ! $current_query ) {
			return;
		}

		if (
			! $this->is_query_set_by_listing( 'lisitng_id', $current_query, $listing_id ) &&
			! $this->is_query_set_by_listing( 'set_query_for_listing_id', $current_query, $listing_id )
		) {
			return;
		}

		$current_query->is_looping = false;
		$current_query->is_component_listing = false;

		// Destroy Query to explicitly remove it from global store
		$current_query->destroy();

		unset( $this->current_query[ $listing_id ] );

	}

	/**
	 * Checks if a query setting is set for a specific listing ID.
	 *
	 * @param string $setting_key The key in the query settings to check.
	 * @param object $query The query object containing the settings.
	 * @param int $listing_id The current listing ID to compare against.
	 *
	 * @return bool Returns true if the setting exists and its value is not equal to $listing_id, otherwise false.
	 */
	public function is_query_set_by_listing( $setting_key, $query, $listing_id ) {
		return isset( $query->settings[ $setting_key ] )
		       && absint( $query->settings[ $setting_key ] ) === absint( $listing_id );
	}

	public function remap_columns( $columns, $settings ) {
		if ( ! empty( $settings['columns:tablet_portrait'] ) ) {
			$columns['tablet'] = absint( $settings['columns:tablet_portrait'] );
		}

		if ( ! empty( $settings['columns:mobile_portrait'] ) ) {
			$columns['mobile'] = absint( $settings['columns:mobile_portrait'] );
		}

		if ( ! empty( $settings['columns:mobile_landscape'] ) ) {
			$columns['mobile_landscape'] = absint( $settings['columns:mobile_landscape'] );
		}

		return $columns;
	}

	public function get_listing_content_cb( $result, $listing_id ) {
		$bricks_data = get_post_meta( $listing_id, BRICKS_DB_PAGE_CONTENT, true );

		if ( ! $bricks_data ) {
			return;
		}

		$is_component      = jet_engine()->listings->components->is_component( $listing_id );
		$has_dynamic_value = ! $is_component && jet_engine()->bricks_views->listing->has_dynamic_value_in_controls( $listing_id );
		$post              = jet_engine()->listings->data->get_current_object();
		$object_id         = jet_engine()->listings->data->get_current_object_id();

		// Retrieve the current query object based on the listing ID.
		$current_query = $this->get_current_query( $listing_id );

		// Set current query loop index to the adjusted value.
		if ( $current_query && ! $current_query->is_component_listing ) {
			$current_query->loop_object = $post;
			$current_query->loop_index  = $object_id;
		}

		ob_start();

		jet_engine()->bricks_views->listing->render_assets( $listing_id, $is_component || $has_dynamic_value );
		$result = ob_get_clean();

		// Prepare flat list of elements for recursive calls
		// Default Bricks logic not used in this case because it reset elements list after rendering
		foreach ( $bricks_data as $element ) {
			\Bricks\Frontend::$elements[ $element['id'] ] = $element;
		}

		// Prevent errors when handling non-post queries with WooCommerce is active
		if ( function_exists( 'WC' ) && \Bricks\Theme::instance()->woocommerce ) {
			remove_filter(
				'bricks/builder/data_post_id',
				[ \Bricks\Theme::instance()->woocommerce, 'maybe_set_post_id' ],
				10, 1
			);
		}

		if ( is_array( $bricks_data ) && count( $bricks_data ) ) {

			foreach ( $bricks_data as $element ) {

				if ( ! empty( $element['parent'] ) ) {
					continue;
				}

				$result .= \Bricks\Frontend::render_element( $element );

			}

		}

		if ( function_exists( 'WC' ) && \Bricks\Theme::instance()->woocommerce ) {
			add_filter(
				'bricks/builder/data_post_id',
				[ \Bricks\Theme::instance()->woocommerce, 'maybe_set_post_id' ],
				10, 1
			);
		}

		// Filter required for the compatibility with default Bricks dynamic data
		return apply_filters(
			'bricks/dynamic_data/render_content',
			$result,
			$post,
			'text'
		);
	}

	// Sets the initial listing ID if it is not already set.
	public function set_initial_listing_id( $content, $post_obj, $i, $widget ) {
		$listing_id = $widget->get_settings( 'lisitng_id' );

		if ( ! empty( $listing_id ) ) {
			$this->current_listing_id = $listing_id;
		}

		return $content;
	}

	public function add_default_settings( $settings ) {
		$settings['_id']                      = '';
		$settings['columns:tablet_portrait']  = '';
		$settings['columns:mobile_portrait']  = '';
		$settings['columns:mobile_landscape'] = '';
		return $settings;
	}

	public function add_widget_settings( $widget_settings, $settings ) {
		$widget_settings['_id'] = $settings['_id'] ?? '';
		return $widget_settings;
	}
}