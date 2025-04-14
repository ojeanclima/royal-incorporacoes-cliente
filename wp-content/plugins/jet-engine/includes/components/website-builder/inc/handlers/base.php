<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;
use Jet_Engine\Query_Builder\Manager as Query_Manager;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Base {

	protected $log = [];
	protected $base_users_query_id = null;

	/**
	 * Store handled entity into log
	 *
	 * @param  array $entity Entity to store into log.
	 */
	public function log_entity( array $entity = [] ) {
		$this->log[] = $entity;
	}

	/**
	 * Returns handler log.
	 * Should be called after creation all of data
	 *
	 * @return array
	 */
	public function get_log() {
		return $this->log;
	}

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	abstract public function handle( array $data = [] );

	/**
	 * Create a new query for a given post type
	 *
	 * @param  string $query_name Name of the entity to create query for.
	 * @param  string $type       Type of query to create.
	 * @param  array  $query_args Query arguments.
	 * @return bool|int
	 */
	public function create_query( $query_name = '', $type = '', $query_args = [] ) {

		$query_name = sprintf( __( 'Base Query for %s', 'jet-engine' ), $query_name );

		$query_settings = [
			'query_type'  => $type,
			'cache_query' => true,
			'name'        => $query_name,
			$type         => $query_args,
		];

		Query_Manager::instance()->data->set_request( apply_filters( 'jet-engine/query-builder/edit-query/request', [
			'name'        => $query_name,
			'slug'        => '',
			'args'        => $query_settings,
			'meta_fields' => [],
		] ) );

		$item_id = Query_Manager::instance()->data->create_item( false );

		if ( $item_id ) {
			do_action( 'jet-engine/query-builder/after-query-update', Query_Manager::instance()->data );
		}

		return $item_id;
	}

	/**
	 * Create a base query to get users.
	 *
	 * @return int
	 */
	public function create_base_users_query() {

		if ( null === $this->base_users_query_id ) {
			$this->base_users_query_id = $this->create_query( 'Users', 'users', [] );
		}

		return $this->base_users_query_id;
	}

	/**
	 * Create a new listing item for given query
	 *
	 * @param  boolean $query_id       [description]
	 * @param  string  $post_type_name [description]
	 * @param  array   $item_data      All information about created item
	 * @return array
	 */
	public function create_listing( $query_id = false, $item_name = '', $item_data = [] ) {

		if ( ! $query_id ) {
			return [];
		}

		$item_name = sprintf( __( 'Base Listing for %s', 'jet-engine' ), $item_name );

		// try to guess listing view
		$listing_view = false;

		if ( $this->has_timber() ) {
			$listing_view = 'twig';
		} elseif ( $this->has_elementor() ) {
			$listing_view = 'elementor';
		} elseif ( $this->has_bricks() ) {
			$listing_view = 'bricks';
		} elseif ( $this->has_blocks() ) {
			$listing_view = 'blocks';
		}

		$listing_view = apply_filters(
			'jet-engine/website-builder/handlers/listing-view',
			$listing_view,
			$query_id,
			$item_name
		);

		if ( ! $listing_view ) {
			return [];
		}

		$args = [
			'listing_source'     => 'query',
			'listing_post_type'  => 'post',
			'listing_tax'        => 'category',
			'_query_id'          => $query_id,
			'repeater_source'    => 'jet_engine',
			'repeater_field'     => '',
			'repeater_option'    => '',
			'cct_type'           => '',
			'cct_repeater_field' => '',
			'template_name'      => $item_name,
			'listing_view_type'  => $listing_view,
			'_is_ajax_form'      => true,
		];

		foreach ( $args as $key => $value ) {
			$_REQUEST[ $key ] = $value;
		}

		$template_data = jet_engine()->post_type->admin_screen->create_listing_template( $args );

		if ( ! empty( $template_data['template_id'] ) ) {
			do_action(
				'jet-engine/listing/set-content/' . $listing_view,
				$this->get_default_listing_data( $item_data ),
				$template_data['template_id']
			);
		}

		return [
			'id'  => ! empty( $template_data['template_id'] ) ? $template_data['template_id'] : false,
			'url' => ! empty( $template_data['edit_url'] ) ? $template_data['edit_url'] : false,
		];
	}

	/**
	 * Check if field requires callback to render correctly - add it to widget settings.
	 *
	 * @param  array  $settings Widget settings.
	 * @param  array  $field    Field data.
	 * @return array
	 */
	public function maybe_add_callback( $settings = [], $field = [] ) {

		$callback = [];

		switch ( $field['type'] ) {

			case 'date':
			case 'datetime':
			case 'datetime-local':
			case 'time':

				$callback['dynamic_field_filter'] = true;
				$callback['filter_callback']      = 'jet_engine_date';
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

				if ( 'date' === $field['type'] ) {
					$format = $date_format;
				} elseif ( 'time' === $field['type'] ) {
					$format = $time_format;
				} else {
					$format = $date_format . ' ' . $time_format;
				}

				$callback['date_format'] = $format;
				break;

			case 'checkbox':

				$callback['dynamic_field_filter'] = true;
				$callback['filter_callback']      = 'jet_engine_render_checkbox_values';
				break;
		}

		if ( ! empty( $callback ) ) {
			$settings = array_merge( $settings, $callback );
		}

		return $settings;
	}

	/**
	 * Get default data to set as listing content for created item.
	 *
	 * @param  array  $item_data [description]
	 * @return array
	 */
	public function get_default_listing_data( $item_data = [] ) {
		return [];
	}

	/**
	 * Check if timber views is currently active
	 *
	 * @return boolean
	 */
	public function has_timber() {

		if ( ! class_exists( '\Jet_Engine\Timber_Views\Integration' ) ) {
			return false;
		}

		$integration = new \Jet_Engine\Timber_Views\Integration();

		return ( $integration->is_enabled() && $integration->has_timber() ) ? true : false;
	}

	/**
	 * Check if Elementor views is currently available
	 *
	 * @return boolean
	 */
	public function has_elementor() {
		return jet_engine()->has_elementor();
	}

	/**
	 * Check if Bricks views is currently available
	 *
	 * @return boolean
	 */
	public function has_bricks() {
		return jet_engine()->bricks_views->has_bricks();
	}

	/**
	 * Check if Blocks views is currently available
	 *
	 * @return boolean
	 */
	public function has_blocks() {
		return \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_blocks_views' );
	}
}
